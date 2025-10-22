<?php
return [
  'provider' => 'ollama',
  'ollama' => [
    'host'    => 'http://127.0.0.1:11434',
    'model'   => 'gemma3:1b',
    'system'  => 'Bạn là trợ lý BĐS. Trả lời ngắn gọn, rõ ràng, luôn bằng tiếng Việt.',
    'timeout' => 20,
  ],
];
