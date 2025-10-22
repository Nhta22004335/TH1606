<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$cfg = require __DIR__.'/../../../config/ai.php';
$hostCfg = rtrim($cfg['ollama']['host'] ?? 'http://127.0.0.1:11434','/');
$model   = (string)($cfg['ollama']['model'] ?? 'gemma3:1b');
$system  = (string)($cfg['ollama']['system'] ?? 'Luôn trả lời tiếng Việt, ngắn gọn.');
$timeout = (int)($cfg['ollama']['timeout'] ?? 20);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'error'=>'method']); exit; }
$msg = trim($_POST['message'] ?? ''); if ($msg==='') { echo json_encode(['ok'=>false,'error'=>'empty']); exit; }
if (!function_exists('curl_init')) { echo json_encode(['ok'=>false,'error'=>'curl_disabled']); exit; }

/* 1) Tìm host Ollama khả dụng: host config -> 127.0.0.1 -> host.docker.internal */
$try = array_unique([$hostCfg, 'http://127.0.0.1:11434', 'http://host.docker.internal:11434']);
$up = null; $lastErr=''; $lastCode=0;
foreach ($try as $h) {
  $ch = curl_init("$h/api/version");
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_CONNECTTIMEOUT=>3,
    CURLOPT_TIMEOUT=>4,
    CURLOPT_IPRESOLVE=>CURL_IPRESOLVE_V4,
  ]);
  $r = curl_exec($ch);
  $lastErr = curl_error($ch);
  $lastCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  curl_close($ch);
  if ($r !== false && $lastCode>=200 && $lastCode<500) { $up=$h; break; }
}
if (!$up) { echo json_encode(['ok'=>false,'error'=>'ollama_unreachable','detail'=>$lastErr?:("HTTP $lastCode")]); exit; }

/* 2) Gửi chat */
if (!isset($_SESSION['ai_chat'])) $_SESSION['ai_chat']=[];
$messages = array_merge([['role'=>'system','content'=>$system]], $_SESSION['ai_chat'], [['role'=>'user','content'=>$msg]]);
$payload = json_encode(['model'=>$model,'messages'=>$messages,'stream'=>false], JSON_UNESCAPED_UNICODE);

$ch = curl_init("$up/api/chat");
curl_setopt_array($ch, [
  CURLOPT_POST=>true,
  CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
  CURLOPT_POSTFIELDS=>$payload,
  CURLOPT_RETURNTRANSFER=>true,
  CURLOPT_CONNECTTIMEOUT=>5,
  CURLOPT_TIMEOUT=>$timeout,
  CURLOPT_IPRESOLVE=>CURL_IPRESOLVE_V4,
]);
$resp = curl_exec($ch);
$code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$err  = curl_error($ch);
curl_close($ch);
if ($resp===false || $code>=400) { echo json_encode(['ok'=>false,'error'=>$err?:("HTTP $code")]); exit; }

$data  = json_decode($resp, true);
$reply = (string)($data['message']['content'] ?? '');
$clean = preg_replace(['/```[\s\S]*?```/u','/^#{1,6}\s*/mu','/[*_`]+/u'], ['','',''], $reply);
$clean = preg_replace('/^\s*[-*•]\s*/mu', "• ", $clean);
$clean = preg_replace('/\n{3,}/u', "\n\n", trim($clean));
if ($clean==='') { echo json_encode(['ok'=>false,'error'=>'no_reply']); exit; }

$_SESSION['ai_chat'] = array_slice(array_merge($_SESSION['ai_chat'], [
  ['role'=>'user','content'=>$msg],
  ['role'=>'assistant','content'=>$clean],
]), -10);

echo json_encode(['ok'=>true,'reply'=>$clean], JSON_UNESCAPED_UNICODE);
