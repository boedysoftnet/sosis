<template>
    <div>
        <div id="printMe">
            <div class="py-2" v-if="penjualan!=null">
                <div class="text-center">
                    <h2 class="text-xl font-semibold">{{penjualan.perusahaan.nama}}</h2>
                    <p class="text-sm">{{penjualan.perusahaan.alamat}}/{{penjualan.perusahaan.telpon}}</p>
                </div>
                <div class="underline uppercase font-semibold text-center text-xl">
                    FAKTUR PENJUALAN
                </div>
                <div class="text-sm uppercase font-semibold pt-4">
                    <div class="grid grid-cols-3">
                        <div>#Nota </div>
                        <div class="col-span-2">: {{penjualan.id}}</div>
                    </div>
                    <div class="grid grid-cols-3">
                        <div>Tanggal </div>
                        <div class="col-span-2">: {{bsoft.DateFormat(penjualan.created_at)}}</div>
                    </div>
                    <div class="grid grid-cols-3">
                        <div>Waktu </div>
                        <div class="col-span-2">: {{bsoft.DateFormat(penjualan.created_at,'hh:mm')}}</div>
                    </div>
                    <div class="grid grid-cols-3">
                        <div>Customer </div>
                        <div class="col-span-2">: {{penjualan.customer.nama}}</div>
                    </div>
                    <div class="grid grid-cols-3">
                        <div>Kasir </div>
                        <div class="col-span-2">: {{penjualan.user.name}}</div>
                    </div>
                </div>
                <div class="border-t mt-2 text-center font-semibold uppercase">
                    Items Pembelanjaan :
                </div>
                <div v-for="it in penjualan.penjualan_detail">
                    <div class="font-semibold">{{it.barang.nama_barang}}</div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="flex">{{bsoft.NumberFormat(it.qty/it.satuan_isi)}} {{it.satuan}}</div>
                        <div class="text-right flex justify-between">
                            <div>x</div>
                            <div>{{bsoft.NumberFormat(it.harga)}}</div>
                        </div>
                        <div class="flex justify-between">
                            <div>=</div>
                            <div class="flex justify-between">
                                <div class="text-right">{{bsoft.NumberFormat(it.sub_total)}}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-2 font-semibold bortder-t border-dashed">
                    <div class="grid grid-cols-2">
                        <div>Sub Total</div>
                        <div class="flex justify-between">
                            <div>:</div>
                            <div>{{bsoft.NumberFormat(penjualan.sub_total)}}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div>Diskon</div>
                        <div class="flex justify-between">
                            <div>:</div>
                            <div>{{bsoft.NumberFormat(penjualan.diskon)}}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div>Total</div>
                        <div class="flex justify-between">
                            <div>:</div>
                            <div>{{bsoft.NumberFormat(penjualan.total)}}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div>Bayar</div>
                        <div class="flex justify-between">
                            <div>:</div>
                            <div>{{penjualan.bank.nama_bank}}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div>Tunai</div>
                        <div class="flex justify-between">
                            <div>:</div>
                            <div>{{bsoft.NumberFormat(penjualan.uang_tunai)}}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2" v-if="penjualan.bank.id!=1">
                        <div>Dana Transfer</div>
                        <div class="flex justify-between">
                            <div>:</div>
                            <div>{{bsoft.NumberFormat(penjualan.dana_transfer)}}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div>Kembali</div>
                        <div class="flex justify-between">
                            <div>:</div>
                            <div>{{bsoft.NumberFormat(penjualan.kembalian)}}</div>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="font-semibold text-orange-500">NB:</div>
                    <div class="text-sm italic">Mohon check barang sebelum meninggalkan toko kami, barang tidak dapat
                        dikembalikan jika tidak ada perjanjian khusus
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";

    const penjualan = ref(null);
    const bsoft = useBoedysoft();
    const emit = defineEmits();
    const ppn = ref(0);

    async function showPrint(penjualan_id) {
        let {data} = await bsoft.axios.get('/penjualan/' + penjualan_id);
        if (data.code === 200) {
            penjualan.value = data.data;
            return setTimeout(() => {
                bsoft.printPeper('printMe');
                emit('onFinish');
            }, 1000);
        }
        return false;
    }

    defineExpose({
        showPrint
    })
</script>

<style lang="scss">
    .font-semibold{
        font-weight: bold;
    }
</style>
