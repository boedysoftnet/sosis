<?php

namespace App\Http\Controllers;


use App\Helpers\Boedysoft;
use App\Models\BarcodePrint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Picqer\Barcode\Barcode;
use Picqer\Barcode\BarcodeGeneratorPNG;


class BarcodePrintController extends Controller
{
    public function showBarcode(){
        $generator=new BarcodeGeneratorPNG('test 12');
        $data=base64_encode($generator->getBarcode("Testsasdfa", $generator::TYPE_CODE_128)) ;
        return "<img src='data:image/png;base64,$data'>";
    }

    public function store(Request  $request){
        $data=BarcodePrint::create([
            'user_id'=>Auth::id(),
            'barang_id'=>$request->barang_id,
            'perusahaan_id'=>Auth::user()->perusahaan->id,
            'jumlah'=>$request->jumlah
        ]);
        return Boedysoft::getJson(200,$data);
    }

    public function index(){
        $data=BarcodePrint::query();
        return Boedysoft::getJson(200,$data->get());
    }
}
