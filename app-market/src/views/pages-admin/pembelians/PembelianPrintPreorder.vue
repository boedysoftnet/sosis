<template>
    <div>
        <!-- SOURCE -->
        <div id="printMe">
            <div class="px-10 py-5" v-if="pembelian!=null">
                <div class="grid grid-cols-2 w-full">
                    <cover-perusahaan :horizontal="true"/>
                    <div class="text-right border-l border-dashed border-black">
                        <div>Mataram, 21 Juli 2022</div>
                        <div>Kepada : Yth </div>
                        <div class="font-semibold">{{pembelian.supplier.nama}}</div>
                        <div>{{pembelian.supplier.alamat}}</div>
                    </div>
                </div>
                <div class="underline uppercase font-semibold text-center text-4xl">
                    PREORDER
                </div>
                <div class="text-lg uppercase font-semibold flex pt-4">
                    <div>Nomor PO : </div>
                    <div>{{pembelian.id}}</div>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="">
                            <th class="border">No</th>
                            <th class="border">Barang</th>
                            <th class="border">Harga</th>
                            <th class="border">Jumlah</th>
                            <th class="border">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="it in pembelian.pembelian_detail">
                            <td class="border p-2">{{it.id}}</td>
                            <td class="border p-2">{{it.barang.nama_barang}}</td>
                            <td class="border px-2 py-2">
                                <div class="flex justify-between">
                                    <div>Rp</div>
                                    <div class="text-right">{{bsoft.NumberFormat(it.harga)}}</div>
                                </div>
                            </td>
                            <td class="border px-2 py-2">
                                <div class="flex justify-between">
                                    <div>Rp</div>
                                    <div class="text-right">{{bsoft.NumberFormat(it.qty)}} {{it.satuan}}</div>
                                </div>
                            </td>
                            <td class="border px-2 py-2">
                                <div class="flex justify-between">
                                    <div>Rp</div>
                                    <div class="text-right">{{bsoft.NumberFormat(it.sub_total)}}</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="mt-5 grid grid-cols-2 gap-3" v-if="pembelian!=null">
                    <div class="space-y-1 border shadow p-4 " v-if="pembelian.piutang===0">
                        <div class="font-semibold uppercase underline">INFORMASI :</div>
                        <div class="flex flex-row justify-between" v-if="pembelian.uang_tunai>0">
                            <div>Uang Tunai</div>
                            <div class="font-semibold">Rp. {{bsoft.NumberFormat(pembelian.uang_tunai)}}</div>
                        </div>
                        <div class="flex flex-row justify-between" v-if="pembelian.dana_transfer>0">
                            <div>Transfer <b class="font-semibold">{{pembelian.bank.nama_bank}}</b></div>
                            <div class="font-semibold">Rp. {{bsoft.NumberFormat(pembelian.dana_transfer)}}</div>
                        </div>
                        <div class="flex flex-row justify-between">
                            <div>Kembalian</div>
                            <div class="font-semibold">Rp. {{bsoft.NumberFormat(pembelian.kembalian)}}</div>
                        </div>
                    </div>
                    <div class="space-y-1 border shadow p-4 " v-if="pembelian.piutang>0">
                        <div class="font-semibold uppercase underline">INFORMASI :</div>
                        <div class="flex flex-row justify-between">
                            <div>Tanggal Jatuh Tempo</div>
                            <div class="font-semibold">{{bsoft.DateFormat(pembelian.tgl_tempo)}}</div>
                        </div>
                        <div class="flex flex-row justify-between" v-if="pembelian.uang_tunai>0">
                            <div>Uang Tunai</div>
                            <div class="font-semibold">Rp. {{bsoft.NumberFormat(pembelian.uang_tunai)}}</div>
                        </div>
                        <div class="flex flex-row justify-between" v-if="pembelian.dana_transfer>0">
                            <div>Transfer <b class="font-semibold">{{pembelian.bank.nama_bank}}</b></div>
                            <div class="font-semibold">Rp. {{bsoft.NumberFormat(pembelian.dana_transfer)}}</div>
                        </div>
                        <div class="flex flex-row justify-between">
                            <div>BESARAN HUTANG</div>
                            <div class="font-semibold">Rp. {{bsoft.NumberFormat(pembelian.sisa_piutang)}}</div>
                        </div>
                        <div v-if="pembelian.catatan!=null">
                            <div class="uppercase underline">Catatan :</div>
                            <div>{{pembelian.catatan}}</div>
                        </div>
                    </div>
                    <div class="border shadow p-4 ">
                        <h2 class="font-semibold uppercase border-b">RINCIAN AKUMULASI</h2>
                        <div class="w-full">
                            <div class="grid grid-cols-3">
                                <div>Sub Total</div>
                                <div class="text-right col-span-2 flex justify-between"><div>:</div> <div class="font-semibold">{{bsoft.NumberFormat(pembelian.sub_total)}}</div></div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Diskon</div>
                                <div class="text-right col-span-2 flex justify-between"><div>:</div> <div class="font-semibold">{{bsoft.NumberFormat(pembelian.diskon)}}</div></div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Total</div>
                                <div class="text-right col-span-2 flex justify-between"><div>:</div> <div class="font-semibold">{{bsoft.NumberFormat(pembelian.total)}}</div></div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>PPN ({{pembelian.ppn_persentase*100}}%)</div>
                                <div class="text-right col-span-2 flex justify-between"><div>:</div> <div class="font-semibold">{{bsoft.NumberFormat(pembelian.ppn)}}</div></div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Total Bayar</div>
                                <div class="text-right col-span-2 flex justify-between"><div>:</div> <div class="font-semibold">{{bsoft.NumberFormat(pembelian.total_setelah_ppn)}}</div></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between mt-10 font-semibold">
                    <div class="text-center flex-1">
                        <div>Tanda Terima,</div>
                        <br>
                        <br>
                        <br>
                        ({{pembelian.supplier.nama}})
                    </div>
                    <div class="text-center flex-1">
                        <div>Hormat Kami,</div>
                        <br>
                        <br>
                        <br>
                        ({{pembelian.user.name}})
                    </div>
                </div>
                <div class="mt-5">
                    <div class="font-semibold text-orange-500">NB:</div>
                    <div class="text-sm italic">Mohon untuk mengirimkan barang sesuai dengan list diatas, Trimakasih ^_^</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";

    const pembelian = ref(null);
    const bsoft = useBoedysoft();
    const emit = defineEmits();
    const ppn =ref(0);

    async function showPrint(pembelian_id) {
        let {data} = await bsoft.axios.get('/pembelian/' + pembelian_id);
        if (data.code === 200) {
            pembelian.value = data.data;
            return setTimeout(()=>{
                bsoft.printPeper('printMe');
                emit('onFinish');
            },500);
        }
        return false;
    }

    defineExpose({
        showPrint
    })
</script>

<style scoped>

</style>
