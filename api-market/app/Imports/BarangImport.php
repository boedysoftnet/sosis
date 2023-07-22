<?php

namespace App\Imports;

use App\Helpers\Boedysoft;
use App\Models\Barang;
use App\Models\BarangKategori;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;

class BarangImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $kategori_id = $this->generateBarangKategori($row[2]);
        $barang = Barang::create([
            'user_id' => request()->user()->id,
            'barang_kategori_id' => $kategori_id,
            'nama_barang' => $row[3],
            'pokok' => $row[5],
            'deskripsi' => 'via import',
            'harga_net' => $row[5],
            'type_jasa' => 0,
        ]);
        $barang->barangSatuan()->create([
            'perusahaan_id'=>Auth::user()->perusahaan->id,
            'satuan'=>$row[4],
            'isi'=>1,
            'harga'=>$row[6],
        ]);
        Boedysoft::setStockHistory($barang,"Import Via Excel",$barang->id,$row[7],"Import Barang via Excel");
        Boedysoft::setStock($barang->id,$row[7]);
        $nilai=floatval($row[5])*floatval($row[7]);
        Boedysoft::setJurnalWithParameter($barang,"Import Barang Via Excel","Persediaan Barang",$nilai,0,"Import Barang ${row[3]}");
        Boedysoft::setJurnalWithParameter($barang,"Import Barang Via Excel","Modal Owner",0,$nilai,"Import Barang ${row[3]}");
        return $barang;
    }

    public function generateBarangKategori($kat)
    {
        $kategori = BarangKategori::where('kategori', $kat);
        if ($kategori->count() > 0) {
            return $kategori->first()->id;
        } else {
            $it = BarangKategori::create([
                'user_id' => Auth::user()->id,
                'kategori' => $kat,
                'deskripsi' => 'via import'
            ]);
            return $it->id;
        }
    }
}
