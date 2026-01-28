<?php
session_start();
$conn = mysqli_connect("localhost","root","","coffee_shop");
if(!$conn) die("DB gagal");

/* GAMBAR MENU (TIDAK DI DATABASE) */
$images = [
    'Americano'   => 'https://images.unsplash.com/photo-1511920170033-f8396924c348?w=400',
    'Latte'       => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400',
    'Espresso'    => 'https://images.unsplash.com/photo-1509785307050-d4066910ec1e?w=400',
    'Cappuccino'  => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400'
];
$default_img = 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400';

/* KERANJANG */
if(!isset($_SESSION['cart'])) $_SESSION['cart']=[];

/* TAMBAH */
if(isset($_GET['add'])){
    $id=(int)$_GET['add'];
    $_SESSION['cart'][$id]=($_SESSION['cart'][$id]??0)+1;
    header("Location:index.php"); exit;
}

/* RESET */
if(isset($_GET['reset'])){
    session_destroy();
    header("Location:index.php"); exit;
}

/* HITUNG TOTAL */
$total=0; $items=[];
foreach($_SESSION['cart'] as $id=>$q){
    $m=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM menu WHERE id_menu=$id"));
    $sub=$m['harga']*$q;
    $total+=$sub;
    $items[]=['n'=>$m['nama_menu'],'q'=>$q,'s'=>$sub];
}

/* BAYAR */
if(isset($_POST['bayar'])){
    $bayar=(int)$_POST['bayar'];
    $total_fix=(int)$_POST['total_fix'];

    if($bayar < $total_fix){
        $_SESSION['err']="❌ Uang tidak cukup";
    } else {
        $_SESSION['ok']="✅ Pembayaran berhasil<br>Kembalian: Rp ".number_format($bayar-$total_fix);
        $_SESSION['cart']=[];
    }
    header("Location:index.php"); exit;
}

$menu=mysqli_query($conn,"SELECT * FROM menu");
?>
<!DOCTYPE html>
<html>
<head>
<title>Coffee Shop POS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{
background:linear-gradient(rgba(0,0,0,.8),rgba(0,0,0,.85)),
url('https://images.unsplash.com/photo-1509042239860-f550ce710b93');
background-size:cover;color:#fff;
}
.menu-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:15px}
.menu-card{background:rgba(255,255,255,.12);border-radius:18px;padding:12px;text-align:center}
.menu-card img{width:100%;height:90px;object-fit:cover;border-radius:12px}
.price{color:#facc15;font-weight:bold}
.cart{background:rgba(0,0,0,.6);border-radius:18px;padding:20px}
.total{font-size:22px;color:#22c55e}
</style>
</head>

<body class="container-fluid p-4">
<h2 class="text-center mb-4">kopi pangku</h2>

<div class="row">

<!-- MENU -->
<div class="col-lg-8">
<div class="menu-grid">
<?php while($m=mysqli_fetch_assoc($menu)){
$img = $images[$m['nama_menu']] ?? $default_img;
?>
<div class="menu-card">
<img src="<?= $img ?>">
<h6><?= $m['nama_menu'] ?></h6>
<div class="price">Rp <?= number_format($m['harga']) ?></div>
<a href="?add=<?= $m['id_menu'] ?>" class="btn btn-warning btn-sm w-100 mt-2">Tambah</a>
</div>
<?php } ?>
</div>
</div>

<!-- KASIR -->
<div class="col-lg-4">
<div class="cart">
<h5> Kasir</h5>

<?php
if(isset($_SESSION['ok'])){
    echo "<div class='alert alert-success'>".$_SESSION['ok']."</div>";
    unset($_SESSION['ok']);
}
if(isset($_SESSION['err'])){
    echo "<div class='alert alert-danger'>".$_SESSION['err']."</div>";
    unset($_SESSION['err']);
}
?>

<hr>
<?php
if(!$items) echo "<i>Keranjang kosong</i>";
foreach($items as $i){
echo "<div class='d-flex justify-content-between'>
<span>{$i['n']} ({$i['q']})</span>
<span>Rp ".number_format($i['s'])."</span>
</div>";
}
?>
<hr>

<div class="total">Total: Rp <?= number_format($total) ?></div>

<form method="post">
<input type="hidden" name="total_fix" value="<?= $total ?>">
<input type="number" name="bayar" class="form-control mt-2" placeholder="Uang Pembeli" required>
<button class="btn btn-success w-100 mt-3">Bayar</button>
<a href="?reset=1" class="btn btn-danger w-100 mt-2">Reset</a>
</form>
</div>
</div>

</div>
</body>
</html>
