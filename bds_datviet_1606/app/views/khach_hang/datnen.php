<?php
require_once __DIR__ . '/../../../config/database.php';
$pdo = ketnoicsdl();
function e($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
function tableCols(PDO $pdo,$t){$s=$pdo->prepare("SELECT lower(column_name) c FROM information_schema.columns WHERE table_schema='public' AND table_name=:t");$s->execute([':t'=>strtolower($t)]);return array_column($s->fetchAll(PDO::FETCH_ASSOC),'c','c');}
function pick($cols,$cands){foreach($cands as $c){if(isset($cols[strtolower($c)]))return strtolower($c);}return null;}

$bCols=tableCols($pdo,'bat_dong_san'); $uCols=tableCols($pdo,'nguoi_dung');
$ownerCol=pick($bCols,['id_chu_so_huu','id_nguoi_dung','id_owner']);
$statusCol=pick($bCols,['trang_thai','status']);
$titleCol =pick($bCols,['tieu_de','tieude','ten','ten_bds','title','mo_ta','mota','noi_dung']);
$areaCol  =pick($bCols,['khu_vuc','khuvuc','vi_tri','thanh_pho','tinh_thanh']);
$addrCol  =pick($bCols,['dia_chi','diachi','address']);
$priceCol =pick($bCols,['gia','gia_ban','gia_tien']); 
$sizeCol  =pick($bCols,['dien_tich','dientich','dien_tich_m2']); 
$dateCol  =pick($bCols,['ngay_dang','created_at','ngay_tao']);

$uNameCol=pick($uCols,['ten_dang_nhap','ho_ten','username','email']); $uPhoneCol=pick($uCols,['so_dt','sdt','dien_thoai','phone']); $uAvtCol=pick($uCols,['avt','avatar','anh_dai_dien']);

$search  = trim($_GET['search'] ?? '');
$page_no = max(1, (int)($_GET['page_no'] ?? 1));
$perPage = 12; $offset = ($page_no-1)*$perPage;

// keywords đất nền
$kwList=['%đất nền%','%dat nen%','%đất %','%dat %','%nền dự án%','%nen du an%'];
$searchCols = array_values(array_filter([$titleCol,$areaCol,$addrCol]));

$where=[]; $params=[];
if($statusCol) $where[] = "b.\"$statusCol\" = 'daduyet'";
if ($searchCols){
  $or=[]; $i=0;
  foreach($searchCols as $c){ foreach($kwList as $k=>$kw){ $p=":kw{$i}_{$k}"; $or[]="b.\"$c\" ILIKE $p"; $params[$p]=$kw; } $i++; }
  if($or) $where[]='('.implode(' OR ',$or).')';
}
if($search!=='' && $searchCols){
  $f=[]; foreach($searchCols as $c){ $f[]="b.\"$c\" ILIKE :search"; }
  $where[]='('.implode(' OR ',$f).')'; $params[':search']="%$search%";
}
$whereSql = $where ? ' WHERE '.implode(' AND ',$where) : '';

$stmtC=$pdo->prepare("SELECT COUNT(*) FROM bat_dong_san b $whereSql");
foreach($params as $k=>$v)$stmtC->bindValue($k,$v,PDO::PARAM_STR);
$stmtC->execute(); $total=(int)$stmtC->fetchColumn();

$select=['b.id'];
$select[]=$titleCol ? "b.\"$titleCol\" AS tieu_de" : "''::text AS tieu_de";
$select[]=$areaCol  ? "b.\"$areaCol\" AS khu_vuc"   : "''::text AS khu_vuc";
$select[]=$addrCol  ? "b.\"$addrCol\" AS dia_chi"   : "''::text AS dia_chi";
$select[]=$priceCol ? "b.\"$priceCol\" AS gia"      : "0::numeric AS gia";
$select[]=$sizeCol  ? "b.\"$sizeCol\" AS dien_tich" : "0::numeric AS dien_tich";
$select[]=$dateCol  ? "b.\"$dateCol\" AS ngay_dang" : "now()::timestamp AS ngay_dang";

$joinUser='';
if($ownerCol){
  $joinUser=" LEFT JOIN nguoi_dung u ON u.id = b.\"$ownerCol\" ";
  $select[]=$uNameCol?"u.\"$uNameCol\" AS ten_dang_nhap":"''::text AS ten_dang_nhap";
  $select[]=$uPhoneCol?"u.\"$uPhoneCol\" AS so_dt":"''::text AS so_dt";
  $select[]=$uAvtCol?"u.\"$uAvtCol\" AS avt":"''::text AS avt";
}else{
  $select[]="''::text AS ten_dang_nhap"; $select[]="''::text AS so_dt"; $select[]="''::text AS avt";
}

$sql="
SELECT ".implode(',',$select).",
       COALESCE(ha.url,'chuacapnhat.jpg') AS anh_dai_dien
FROM bat_dong_san b
$joinUser
LEFT JOIN LATERAL (SELECT url FROM hinh_anh_bds WHERE id_bds=b.id ORDER BY ngay_tao ASC LIMIT 1) ha ON TRUE
$whereSql
ORDER BY ngay_dang DESC
LIMIT :limit OFFSET :offset";
$stmt=$pdo->prepare($sql);
foreach($params as $k=>$v)$stmt->bindValue($k,$v,PDO::PARAM_STR);
$stmt->bindValue(':limit',(int)$perPage,PDO::PARAM_INT);
$stmt->bindValue(':offset',(int)$offset,PDO::PARAM_INT);
$stmt->execute(); $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

$imgPrefix='/storage/bds/';
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="utf-8"><title>Đất nền & Dự án</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-50 text-gray-800">
<div class="max-w-7xl mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold mb-4">Đất nền & Dự án</h1>
  <form method="GET" class="mb-6 flex gap-2">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Tìm theo tiêu đề, khu vực..." class="flex-1 border px-3 py-2 rounded" />
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Tìm</button>
  </form>

  <?php if(!$rows): ?>
    <p class="text-gray-500">Chưa có sản phẩm đất nền phù hợp.</p>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach($rows as $p): $img=$imgPrefix.e($p['anh_dai_dien']); ?>
      <div class="bg-white rounded-lg shadow p-4">
        <a href="chitiet_bds.php?id=<?= e($p['id']) ?>">
          <img src="<?= e($img) ?>" alt="<?= e($p['tieu_de']) ?>" class="w-full h-44 object-cover rounded-md mb-3">
        </a>
        <h3 class="text-lg font-semibold"><?= e($p['tieu_de']) ?></h3>
        <p class="text-sm text-gray-600"><?= e($p['khu_vuc']) ?> • <?= e($p['dien_tich']) ?> m²</p>
        <p class="text-red-600 font-bold mt-2"><?= e(number_format((float)$p['gia'],0,',','.')) ?> VNĐ</p>
        <div class="mt-3 flex items-center justify-between text-sm text-gray-500">
          <span>Người đăng: <?= e($p['ten_dang_nhap']) ?></span>
          <span><?= date('d/m/Y', strtotime($p['ngay_dang'])) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php $pages=max(1,(int)ceil($total/$perPage)); if($pages>1): ?>
    <div class="mt-6 flex justify-center items-center space-x-2">
      <?php for($i=1;$i<=$pages;$i++): ?>
      <a href="?<?= http_build_query(['search'=>$search,'page_no'=>$i]) ?>" class="px-3 py-1 rounded <?= $i===$page_no?'bg-blue-600 text-white':'bg-white border' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>
