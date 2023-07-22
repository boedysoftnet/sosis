<template>
    <transition>
        <div v-if="dialog && dataRef.barang"
             class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50 h-max-[450px]">
                <div class="py-1 px-5 font-semibold border-b flex justify-between items-center">
                    <span>Real Hasil Proses Produksi</span>
                    <span class="text-basic-400">Stock Akhir : ({{dataRef.barang.sisa_stock}} {{dataRef.barang.satuan}})</span>
                </div>
                <div class="p-3 border m-3 shadow bg-basic-300 bg-opacity-20">
                    <div class="flex justify-between">
                        <label for="">Jumlah Diperkirakan</label>
                        <div class="font-semibold">{{detailSelection.estimasi}} {{detailSelection.barang.satuan}}</div>
                    </div>
                    <div class="flex justify-between">
                        <label for="">Total Biaya Produksi</label>
                        <div class="font-semibold">Rp. {{bsoft.NumberFormat(detailSelection.total)}}</div>
                    </div>
                    <div class="flex justify-between">
                        <label for="">PPN</label>
                        <div class="font-semibold">Rp. {{bsoft.NumberFormat(detailSelection.ppn)}}</div>
                    </div>
                    <div class="flex justify-between">
                        <label for="">Total Biaya Produksi Setelah PPN</label>
                        <div class="font-semibold">Rp. {{bsoft.NumberFormat(detailSelection.total_setelah_ppn)}}</div>
                    </div>
                    <div class="flex justify-between">
                        <label for="">Estimasi HPP</label>
                        <div class="font-semibold">Rp. {{bsoft.NumberFormat(detailSelection.estimasi_hpp)}}</div>
                    </div>
                    <div v-if="hasil_produksi===0"
                         class="p-3 border border-basic-300 rounded shadow text-center font-semibold mt-2">
                        FIX PRODUKSI SESUAI ESTIMASI
                    </div>
                    <div v-if="isBonus && hasil_produksi!=0">
                        <div class="flex justify-between text-green-500 font-semibold">
                            <label for="">Bonus Hasil Produksi</label>
                            <div class="font-semibold">{{bsoft.NumberFormat(hasil_produksi)}}
                                {{detailSelection.barang.satuan}}
                            </div>
                        </div>
                        <div class="flex justify-between text-green-500 font-semibold">
                            <label for="">Bonus Profit</label>
                            <div class="font-semibold">
                                Rp.{{bsoft.NumberFormat(detailSelection.estimasi_hpp*hasil_produksi)}}
                            </div>
                        </div>
                    </div>
                    <div v-if="!isBonus && hasil_produksi!=0">
                        <div class="flex justify-between text-red-500 font-semibold">
                            <label for="">Produksi Menyusut</label>
                            <div class="font-semibold">{{bsoft.NumberFormat(hasil_produksi)}}
                                {{detailSelection.barang.satuan}}
                            </div>
                        </div>
                        <div class="flex justify-between text-red-500 font-semibold">
                            <label for="">Kerugian Atas Penyusutan</label>
                            <div class="font-semibold">
                                Rp.{{bsoft.NumberFormat(detailSelection.estimasi_hpp*hasil_produksi)}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="py-3 px-5 border m-3 shadow bg-basic-300 bg-opacity-20">
                    <form @submit.prevent="konfirmasiFinish">
                        <div class="flex justify-between flex-col text-right font-semibold mt-3 uppercase">
                            <label for="" class="text-sm">Nilai Harga Pokok Sebenarnya</label>
                            <div class="font-semibold text-2xl capitalize">Rp.
                                {{bsoft.NumberFormat((detailSelection.total_setelah_ppn/form.qty).toFixed(0))}}
                            </div>
                        </div>
                        <div class="w-full mt-2">
                            <input type="number" ref="refQuantity" step="any" v-model="form.qty"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full text-right"
                                   placeholder="Jumlah Real Hasil Produksi">
                            <ValidateError :errors="errors" name="diskonGlobal"></ValidateError>
                        </div>
                        <div class="text-xs text-red-500 py-2">
                            NB: Setelah konfirmasi produksi, data produksi sudah tidak dapat di edit kembali.. mohon memastikan hasil produksi sudah benar..!
                        </div>
                        <div class="pt-3 text-right border-t border-basic-500 mt-2 border-dashed">
                            <button type="button" class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50"
                                    @click.prevent="dismisDialog"><span class="dx-icon-back "></span> Batal
                            </button>
                            <button class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50">Konfirmasi Hasil Produksi
                                <span class="fa fa-arrow-right"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>
<script setup>
    import {ref, reactive, onMounted, onActivated, computed, watch} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';

    const dialog = new ref(false);
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const refQuantity = ref();
    const detailSelection = ref();
    const dataRef = reactive({
        barang: null
    })
    const hasil_produksi = ref(computed(() => (parseFloat(form.qty) - parseFloat(detailSelection.value.estimasi))))
    const form = reactive({
        qty: 0,
        satuan: ''
    });

    const isBonus = ref(computed(() => (parseFloat(form.qty) - parseFloat(detailSelection.value.estimasi)) > 0))


    function showDialog(id) {
        dialog.value = true;
        errors.value = {};
        bsoft.axios.get(`/produksi/${id}`).then(res => {
            if (res.data.code === 200) {
                detailSelection.value = res.data.data;
                dataRef.barang = detailSelection.value.barang;
                form.satuan = detailSelection.value.satuan;
                form.qty = detailSelection.value.real_hasil;
            }
        })
        setTimeout(() => {
            refQuantity.value.focus();
        }, 100)
    }

    function dismisDialog() {
        emit('onRefresh');
        dialog.value = false;
    }

    function konfirmasiFinish() {
        let data = {
            hasil_produksi: form.qty
        }
        bsoft.axios.post('/produksi/finish/' + detailSelection.value.id, data).then(res => {
            if (res.data.code === 200) Swal.fire({
                icon:"success",
                title:"Produksi Selesai",
                text:"Trimakasih konfirmasi produksi telah selesai..!",
                timer:1000,
                showConfirmButton:false
            }).then(dismisDialog);
        })
    }

    defineExpose({
        showDialog,
    });
</script>
<style scoped>

</style>
