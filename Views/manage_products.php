<?php
session_start();
require_once '../config/Database.php';
require_once '../Controllers/ProductController.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare("SELECT id, nama_toko FROM shops WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$shop){
    echo "<script>alert('Buat toko dulu!'); window.location='create_shop.php';</script>";
    exit;
}

$productController = new ProductController();
$products = $productController->getProductsByShop($shop['id']);


$total_produk = count($products);
$total_stok = 0;
$kategori_unik = [];

foreach($products as $p) {
    $total_stok += $p['stok'];
    if(!in_array($p['nama_kategori'], $kategori_unik)) {
        $kategori_unik[] = $p['nama_kategori'];
    }
}
$total_kategori = count($kategori_unik);


$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Toko - <?php echo htmlspecialchars($shop['nama_toko']); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">

    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            
            <div class="flex items-center gap-2 group cursor-default">
                <div class="bg-blue-600 text-white p-2 rounded-lg transition shadow-sm">
                    <i class="fas fa-store"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-bold text-slate-800 tracking-tight leading-none"><?php echo htmlspecialchars($shop['nama_toko']); ?></span>
                    
                </div>
            </div>

            <div class="hidden md:flex flex-1 mx-10">
                </div>

            <div class="flex items-center gap-4">
                
                <a href="../index.php" class="text-slate-500 hover:text-blue-600 font-medium text-sm flex items-center gap-2 transition px-2 py-1 rounded-lg hover:bg-slate-50">
                    <i class="fas fa-arrow-left"></i> <span class="hidden md:inline">Halaman Utama</span>
                </a>

                <div class="h-6 w-px bg-slate-200 hidden md:block"></div>

                <div class="relative">
                    <button id="navProfileTrigger" class="flex items-center gap-2 hover:bg-slate-50 p-1 pr-2 rounded-full transition group cursor-pointer border border-transparent hover:border-slate-200">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">
                            <?php 
                                $parts = explode(" ", $nama);
                                echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                            ?>
                        </div>
                        <span class="text-sm font-semibold text-slate-700 hidden md:block max-w-[100px] truncate"><?php echo htmlspecialchars($nama); ?></span>
                        <i class="fas fa-chevron-down text-xs text-slate-400 ml-1 transition-transform duration-200" id="navChevron"></i>
                    </button>

                    <div id="navProfileDropdown" class="hidden absolute right-0 mt-3 w-64 bg-white rounded-xl shadow-xl border border-slate-100 overflow-hidden z-50 origin-top-right">
                        <div class="p-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm"><?php echo htmlspecialchars($nama); ?></p>
                                <p class="text-xs text-slate-500 capitalize">Penjual</p>
                            </div>
                        </div>

                        <div class="p-2 space-y-1">
                            <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 rounded-lg transition group">
                                <div class="w-6 text-center"><i class="fas fa-store text-slate-400 group-hover:text-blue-500"></i></div>
                                Pengaturan Toko
                            </a>
                            <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 rounded-lg transition group">
                                <div class="w-6 text-center"><i class="fas fa-user-circle text-slate-400 group-hover:text-blue-500"></i></div>
                                Profil Saya
                            </a>
                            
                            <div class="h-px bg-slate-100 my-1 mx-2"></div>
                            
                            <a href="../logout.php" class="flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition group">
                                <div class="w-6 text-center"><i class="fas fa-sign-out-alt text-red-400 group-hover:text-red-500"></i></div>
                                Keluar
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 py-8 animate-enter">
        
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Overview</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    <i class="fas fa-box text-xl"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase font-semibold">Total Produk</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?php echo $total_produk; ?></h3>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                    <i class="fas fa-cubes text-xl"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase font-semibold">Total Stok</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?php echo $total_stok; ?></h3>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="p-3 bg-purple-50 text-purple-600 rounded-lg">
                    <i class="fas fa-tags text-xl"></i>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase font-semibold">Kategori</p>
                    <h3 class="text-2xl font-bold text-slate-800"><?php echo $total_kategori; ?></h3>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-lg font-bold text-slate-800">Daftar Produk</h2>
                <div class="flex gap-3 w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <input type="text" id="searchInput" placeholder="Cari nama produk..." class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    </div>
                    <button onclick="$('#modalAdd').removeClass('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2 whitespace-nowrap shadow-md shadow-blue-200">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold tracking-wide">
                        <tr>
                            <th class="p-4 pl-6">Produk</th>
                            <th class="p-4">Kategori</th>
                            <th class="p-4">Harga</th>
                            <th class="p-4">Stok</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-right pr-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                        <?php foreach($products as $p): ?>
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-md bg-slate-100 overflow-hidden border border-slate-200 flex-shrink-0">
                                        <img src="../assets/images/<?php echo $p['gambar']; ?>" class="w-full h-full object-cover" alt="img">
                                    </div>
                                    <span class="font-medium text-slate-800 group-hover:text-blue-600 transition"><?php echo $p['nama_produk']; ?></span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                    <?php echo $p['nama_kategori']; ?>
                                </span>
                            </td>
                            <td class="p-4 font-semibold text-slate-700">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></td>
                            <td class="p-4"><?php echo $p['stok']; ?></td>
                            <td class="p-4 text-center">
                                <?php if($p['stok'] > 0): ?>
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Ready
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Habis
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right pr-6">
                                <div class="flex justify-end gap-2">
                                    <button class="text-slate-400 hover:text-blue-600 p-2 rounded-full hover:bg-blue-50 transition">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button onclick="deleteProduct(<?php echo $p['id']; ?>)" class="text-slate-400 hover:text-red-600 p-2 rounded-full hover:bg-red-50 transition">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(count($products) == 0): ?>
                            <tr>
                                <td colspan="6" class="p-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-box-open text-4xl mb-2 opacity-50"></i>
                                        <p class="text-sm">Belum ada produk di tokomu.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modalAdd" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl transform transition-all scale-100">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800">Tambah Produk Baru</h2>
                <button onclick="$('#modalAdd').addClass('hidden')" class="text-slate-400 hover:text-slate-600 transition"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div class="p-6">
                <form id="formAddProduct" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" value="add">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Nama Produk</label>
                        <input type="text" name="nama_produk" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"  required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Kategori</label>
                            <select name="category_id" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="1">Elektronik</option>
                                <option value="2">Pakaian</option>
                                <option value="3">Hobi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Stok Awal</label>
                            <input type="number" name="stok" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Harga (Rp)</label>
                        <input type="number" name="harga" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Deskripsi</label>
                        <textarea name="deskripsi" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" rows="3"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Foto Produk</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-24 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 transition">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="fas fa-cloud-upload-alt text-2xl text-slate-400 mb-1"></i>
                                    <p class="text-[10px] text-slate-500">Klik untuk upload (JPG/PNG)</p>
                                </div>
                                <input id="dropzone-file" type="file" name="gambar" class="hidden" required />
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition shadow-lg shadow-blue-200 mt-4">Simpan Produk</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            
            $('#navProfileTrigger').click(function(e){
                e.stopPropagation();
                $('#navProfileDropdown').slideToggle(200);
                $('#navChevron').toggleClass('rotate-180');
            });

            $(document).click(function(){
                $('#navProfileDropdown').slideUp(200);
                $('#navChevron').removeClass('rotate-180');
            });

            $('#navProfileDropdown').click(function(e){
                e.stopPropagation();
            });

           
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            
            $('#formAddProduct').on('submit', function(e){
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: '../api/product.php',
                    type: 'POST',
                    data: formData,
                    success: function (data) {
                        if(data.status === 'success'){
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1500 }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    },
                    cache: false, contentType: false, processData: false
                });
            });
        });

        function deleteProduct(id) {
            Swal.fire({
                title: 'Hapus?', text: "Data tidak bisa kembali.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('../api/product.php', { action: 'delete', product_id: id }, function(data) {
                        if(data.status === 'success') location.reload(); else Swal.fire('Gagal', data.message, 'error');
                    }, 'json');
                }
            })
        }
    </script>
</body>
</html>