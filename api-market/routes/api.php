<?php

use App\Http\Controllers\AkunPerkiraanController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKategoriController;
use App\Http\Controllers\BarcodePrintController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\HutangSetoranController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\KasTransaksiController;
use App\Http\Controllers\NavigatorController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Models\BarangKategori;
use App\Models\BarcodePrint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('v1')->group(function () {
    Route::get('now',function (){
        return now()->format('d M Y, H:i');
    });
    Route::controller(UserController::class)->group(function () {
        Route::post('user/auth', 'authUser');
        Route::get('user/valid', 'validUser')->name('login');
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(JurnalController::class)->group(function () {
            Route::get('jurnal/akun/{akunPerkiraan}', 'showArusPembukuan')->name('jurnal.akun');
            Route::get('jurnal/arus-pembukuan/{akunPerkiraan}', 'showJurnalPrint');
            Route::post('reset-database', 'resetDatabase')->name('reset.database');
        });
        Route::controller(UserController::class)->group(function () {
            Route::get('user/logout', 'logout')->name('user.logout');
            Route::get('user/profile', 'profile')->name('user.profile');
            Route::post('user/update/{user}', 'update')->name('user.update.data');
            Route::resource('user', UserController::class);
        });

        Route::controller(NavigatorController::class)->group(function () {
            Route::get('navigator', 'index');
        });

        Route::controller(BarangKategoriController::class)->group(function () {
        });
        Route::resource('barang-kategori', BarangKategoriController::class);

        Route::controller(BarangController::class)->group(function () {
            Route::post('barang/import', 'importData');
            Route::get('barang/satuan/{barang}', 'showWithSatuan');
            Route::resource('barang', BarangController::class);
        });

        Route::controller(PerusahaanController::class)->group(function () {
            Route::post('perusahaan/{perusahaan}', 'update');
            Route::resource('perusahaan', PerusahaanController::class);
        });
        Route::controller(SupplierController::class)->group(function () {
            Route::resource('supplier', SupplierController::class);
        });
        Route::controller(BankController::class)->group(function () {
            Route::resource('bank', BankController::class);
        });
        Route::controller(PembelianController::class)->group(function () {
            Route::post('pembelian/create', 'createPembelian')->name('pembelian.create');
            Route::post('pembelian/detail/add/{pembelian}', 'addBarangDetail')->name('pembelian.detail.add');
            Route::get('pembelian/detail/{pembelian}', 'getPembelianDetail')->name('pembelian.detail');
            Route::delete('pembelian/detail/hapus/{pembelianDetail}', 'detailRemove')->name('pembelian.detail.hapus');
            Route::post('pembelian/diskon/{pembelian}', 'setDiskonGlobal')->name('pembelian.setdiskon');
            Route::post('pembelian/supplier/{pembelian}', 'setSupplier')->name('pembelian.setsupplier');
            Route::post('pembelian/detail/edit/{pembelianDetail}', 'editDetailPembelian')->name('pembelian.editdetailpembelian');
            Route::post('pembelian/detail/harga/{pembelianDetail}', 'editHarga')->name('pembelian.editharga');
            Route::post('pembelian/detail/expaid/{pembelianDetail}', 'setExpaidDate')->name('pembelian.expaiddate');
            Route::post('pembelian/preorder/{pembelian}', 'simpanPreorder')->name('pembelian.simpanpreorder');
            Route::post('pembelian/simpan/{pembelian}', 'simpanPembelian')->name('pembelian.simpanpembelian');
            Route::get('pembelian/barang/masuk', 'getBarangMasuk')->name('pembelian.barang.masuk');
            Route::get('pembelian/barang/detail/{barang}', 'getPembelianBarangDetail')->name('pembelian.barang.detail');
            Route::get('pembelian/informasi/expaid', 'getExpaidInformasi')->name('pembelian.expaid.informasi');
            Route::get('pembelian/piutang/history', 'hutangHistorySetoran')->name('pembelian.piutang.history.setoran');
            Route::get('pembelian/bukti-setoran/{hutangSetoran}', 'getSetoran')->name('pembelian.setoran');
            Route::resource('pembelian', PembelianController::class);
        });
        Route::controller(AkunPerkiraanController::class)->group(function () {
            Route::get('akun-perkiran/show-transaksi', 'showTransaksi');
            Route::get('rugi-laba', 'getRugiLaba');
            Route::resource('akun-perkiraan', AkunPerkiraanController::class);
        });
        Route::controller(StockOpnameController::class)->group(function () {
            Route::resource('stock/opname', StockOpnameController::class);
        });
        Route::controller(HutangSetoranController::class)->group(function () {
            Route::resource('hutang/setoran', HutangSetoranController::class);
        });
        Route::controller(CustomerController::class)->group(function () {
            Route::get('customer/daftar-point', [CustomerController::class, 'getPointPerolehan']);
            Route::post('customer/tukar-point', [CustomerController::class, 'simpanTukarPoint']);
            Route::resource('customer', CustomerController::class);
        });
        Route::controller(PenjualanController::class)->group(function () {
            Route::post('penjualan/generate', 'newPenjualan')->name('penjualan.generate');
            Route::post('penjualan/edit/{penjualan}', 'newPenjualan')->name('penjualan.edit');
            Route::post('penjualan/addlist/{penjualan}', 'addList')->name('penjualan.addlist');
            Route::delete('penjualan/destory/{penjualanDetail}', 'destoryItem')->name('penjualan.destory.item');
            Route::post('penjualan/detail/edit/{penjualanDetail}', 'editItemPenjualan')->name('penjualan.detail.edit');
            Route::post('penjualan/diskon/{penjualan}', 'setDiskonGlobal')->name('penjualan.diskon.global');
            Route::post('penjualan/preorder/{penjualan}', 'simpanPreorder')->name('penjualan.preorder');
            Route::post('penjualan/customer/{penjualan}', 'setCusotmer')->name('penjualan.setcustomer');
            Route::post('penjualan/simpan/{penjualan}', 'simpanPenjualan')->name('penjualan.simpan');
            Route::get('penjualan/item-terjual', 'itemTerjual')->name('penjualan.itemterjual');
            Route::post('penjualan/piutang/setoran', 'setoranPiutang')->name('penjualan.setoran.piutang');
            Route::get('penjualan/piutang/history', 'piutangHistorySetoran')->name('penjualan.piutang.history.setoran');
            Route::get('/penjualan/statistik/barang', 'statistikBarang')->name('penjualan.statisk.barang');
            Route::resource('penjualan', PenjualanController::class);
        });
        Route::controller(DepartementController::class)->group(function () {
            Route::resource('departement', DepartementController::class);
        });
        Route::controller(ProduksiController::class)->group(function () {
            Route::get('produksi/generate', 'buatProduksi')->name('produksi.generate');
            Route::get('produksi/edit/{produksi}', 'buatProduksi')->name('produksi.edit');
            Route::post('produksi/detail/add/{produksi}', 'addBahanBaku')->name('produksi.detail.add');
            Route::delete('produksi/detail/destroy/{produkDetail}', 'destroyDetail')->name('produksi.detail.destroy');
            Route::delete('/produksi/delete/{produksi}', 'hapusProduksi');
            Route::post('produksi/detail/edit/{produkDetail}', 'setJumlahBahan')->name('produksi.edit.jumlah.bahan');
            Route::post('produksi/update/{produksi}', 'simpanProduksi')->name('produksi.finish');
            Route::post('produksi/finish/{produksi}', 'konfirmasiProduksi')->name('produksi.konfirmasi.produksi');
            Route::resource('produksi', ProduksiController::class);
        });
        Route::controller(KasTransaksiController::class)->group(function () {
            Route::resource('kas', KasTransaksiController::class);
        });
        Route::resource('barcode-print', BarcodePrintController::class);
    });
});
