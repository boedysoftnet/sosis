<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/3 shadow bg-basic-50 h-max-[450px]">
                <div class="py-1 px-5 font-semibold border-b flex justify-between items-center">
                    <span>Edit Quantity Bahan Baku</span>
                    <span class="text-basic-400">Stock Akhir : ({{dataRef.barang.sisa_stock}} {{dataRef.barang.satuan}})</span>
                </div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" ref="refQuantity" step="any" v-model="form.qty"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full"
                                   placeholder="NB: 10%">
                            <ValidateError :errors="errors" name="diskonGlobal"></ValidateError>
                            <select class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full"
                                    v-model="form.satuan">
                                <option :value="it.satuan" v-for="it in dataRef.barang.barang_satuan">{{it.satuan}}
                                    ({{it.isi}})
                                </option>
                            </select>
                        </div>
                        <div class="pt-3 text-right">
                            <button type="button" class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50"
                                    @click.prevent="dismisDialog"><span class="dx-icon-back "></span> Batal
                            </button>
                            <button class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50"><span
                                    class="dx-icon-save"></span> Terapkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>
<script setup>
    import {ref, reactive, onMounted, onActivated} from 'vue';
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
    const form = reactive({
        qty: 0,
        satuan: ''
    });

    function showDialog(keydetail) {
        dialog.value = true;
        errors.value = {};
        detailSelection.value = keydetail;
        bsoft.axios.get(`/barang/${keydetail.barang_id}`).then(res => {
            if (res.data.code === 200) {
                dataRef.barang = res.data.data;
                form.satuan = keydetail.satuan;
                form.qty = keydetail.jumlah;
            }
        })
        setTimeout(() => {
            refQuantity.value.focus();
        }, 100)
    }

    function dismisDialog() {
        dialog.value = false;
    }

    function sukses(res) {
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: res.message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            emit('onRefresh');
            dismisDialog();
        })
    }

    async function simpan() {
        let frm = {
            barang_id: dataRef.barang.id,
            qty: form.qty,
            satuan: form.satuan,
            satuan_isi: dataRef.barang.barang_satuan.filter(e => e.satuan === form.satuan)[0].isi
        };
        let {data} = await bsoft.axios.post(`/produksi/detail/edit/${detailSelection.value.id}`, frm);
        if (data.code === 200) {
            sukses(data);
        } else if (data.code === 201) {
            Swal.fire({
                icon:"warning",
                title:"Peringatan..!",
                text: data.message
            })
        }else{
            errors.value = data.data;
        }
    }


    defineExpose({
        showDialog,
    });
</script>
<style scoped>

</style>
