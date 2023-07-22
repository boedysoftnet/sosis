<template>
    <div>
        <!-- SOURCE -->
        <div id="printSuratJalan">
            <div class="px-10 py-5" v-if="penjualan!=null">
                <div class="grid grid-cols-2 w-full ">
                    <cover-perusahaan :horizontal="true"/>
                    <div class="text-right border-l border-dashed border-black">
                        <div>Mataram, {{bsoft.DateFormat(penjualan.created_at)}}</div>
                        <div>Kepada : Yth</div>
                        <div class="font-semibold">{{penjualan.customer.nama}}</div>
                        <div>{{penjualan.customer.alamat}}</div>
                    </div>
                </div>
                <div class="underline text-4xl font-semibold text-center mt-5">
                    SURAT JALAN
                </div>
                <div class="text-lg font-semibold flex">
                    <div>No :</div>
                    <div>{{penjualan.id}}</div>
                </div>
                <div>
                    Harap diterima dengan baik barang-barang tersebut dibawah ini :
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="uppercase">
                            <th class="border w-10">No</th>
                            <th class="border">Barang</th>
                            <th class="border w-20">Jumlah</th>
                            <th class="border">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="it in penjualan.penjualan_detail">
                            <td class="border p-2">{{it.id}}</td>
                            <td class="border p-2">{{it.barang.nama_barang}}</td>
                            <td class="border px-2 py-2">
                                <div class="text-right">
                                    <div class="text-right">{{bsoft.NumberFormat(it.qty/it.satuan_isi)}} {{it.satuan}}</div>
                                </div>
                            </td>
                            <td class="border px-2 py-2">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex justify-between mt-10 font-semibold">
                    <div class="text-center flex-1">
                        <br>
                        <div>Tanda Terima,</div>
                        <br>
                        <br>
                        <br>
                        ({{penjualan.customer.nama}})
                    </div>
                    <div class="text-center flex-1">
                        <br>
                        <div>Pengirim,</div>
                        <br>
                        <br>
                        <br>
                        ({{penjualan.user.name}})
                    </div>
                    <div class="text-center flex-1">
                        <div>Mataram, 21 Juli 2022</div>
                        <div>Mengetahui</div>
                        <br>
                        <br>
                        <br>
                        (___________________________)
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

    async function showPrint(penjualan_id) {
        let {data} = await bsoft.axios.get('/penjualan/' + penjualan_id);
        if (data.code === 200) {
            penjualan.value = data.data;
            return setTimeout(()=>{
                bsoft.printPeper('printSuratJalan');
                emit('onFinish');
            },1000);
        }
        return false;
    }

    defineExpose({
        showPrint
    })
</script>

<style scoped>

</style>
