<template>
    <div>
        <!-- SOURCE -->
        <div id="printMe">
            <div class="px-10 py-5" v-if="setoran!=null">
                <cover-perusahaan :horizontal="true"/>
                <div class="underline uppercase font-semibold text-center text-4xl mt-5 mb-3">
                    KWITANSI BUKTI SETORAN
                </div>
                <div>
                    <div class="grid grid-cols-5">
                        <div>Kepada Supplier</div>
                        <div class="col-span-4">{{setoran.pembelian.supplier.nama}}</div>
                    </div>
                    <div class="grid grid-cols-5">
                        <div>Alamat</div>
                        <div class="col-span-4">{{setoran.pembelian.supplier.alamat}}</div>
                    </div>
                    <div class="grid grid-cols-5">
                        <div>Telpon</div>
                        <div class="col-span-4">{{setoran.pembelian.supplier.telpon}}</div>
                    </div>
                    <div class="grid grid-cols-5">
                        <div>Senilai</div>
                        <div class="col-span-4">Rp. {{bsoft.NumberFormat(setoran.jumlah)}}</div>
                    </div>
                    <div class="grid grid-cols-1 space-y-1 py-3 border border black text-center mt-4">
                        <div>Terbilang</div>
                        <div class="uppercase font-semibold italic mt-2">{{bsoft.terbilang(setoran.jumlah)}}</div>
                    </div>
                    <div class="grid grid-cols-5">
                        <div>Catatan</div>
                        <div class="col-span-4">{{setoran.catatan}}</div>
                    </div>
                </div>

                <div class="flex justify-between mt-10 font-semibold">
                    <div class="text-center flex-1">
                        <br>
                        <div>Penerima,</div>
                        <br>
                        <br>
                        <br>
                        ({{setoran.penerima}})
                    </div>
                    <div class="text-center flex-1">
                        <div>Mataram 22 Juli 2022</div>
                        <div>Hormat Kami,</div>
                        <br>
                        <br>
                        <br>
                        ({{setoran.user.name}})
                    </div>
                </div>
                <div class="mt-5">
                    <div class="font-semibold text-orange-500">NB:</div>
                    <div class="text-sm italic">Pastikan jumlah uang telah diterima sesuai dengan nominal tertera diatas, komplain tidak dapat diterima apabila sudah meninggalkan kantor</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";

    const setoran = ref(null);
    const bsoft = useBoedysoft();
    const emit = defineEmits();
    const ppn =ref(0);

    async function showPrint(id) {
        let {data} = await bsoft.axios.get('/pembelian/bukti-setoran/' + id);
        if (data.code === 200) {
            setoran.value = data.data;
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
