<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50 h-max-[450px]">
                <div class="py-2 px-5 font-semibold border-b flex flex-col items-center justify-between">
                    <h2>SETORAN HUTANG</h2>
                    <p class="text-sm italic text-basic-400">Anda dapat menyetorkan hutang, secara bertahap ataupun
                        sekaligus</p>
                </div>
                <div class="py-3 px-5">
                    <div>
                        <div class="flex">
                            <label for="" class="w-52 text-left">Nama Supplier</label>
                            <div class="flex-1 text-left">{{dataRef.pembelian.supplier.nama}}</div>
                        </div>
                        <div class="flex">
                            <label for="" class="w-52 text-left">Alamat</label>
                            <div class="flex-1 text-left">{{dataRef.pembelian.supplier.alamat}}</div>
                        </div>
                        <div class="flex">
                            <label for="" class="w-52 text-left">Telpon</label>
                            <div class="flex-1 text-left">{{dataRef.pembelian.supplier.telpon}}</div>
                        </div>
                        <div class="flex">
                            <label for="" class="w-52 text-left">Total</label>
                            <div class="flex-1 text-left">
                                {{bsoft.NumberFormat(dataRef.pembelian.total_setelah_ppn,0)}}
                            </div>
                        </div>
                        <div class="flex">
                            <label for="" class="w-52 text-left">Hutang</label>
                            <div class="flex-1 text-left">{{bsoft.NumberFormat(dataRef.pembelian.hutang,0)}}</div>
                        </div>
                        <div class="flex">
                            <label for="" class="w-52 text-left">Sisa</label>
                            <div class="flex-1 text-left">{{bsoft.NumberFormat(dataRef.pembelian.sisa_hutang,0)}}</div>
                        </div>
                        <div class="flex">
                            <label for="" class="w-52 text-left">Terbayar</label>
                            <div class="flex-1 text-left">{{bsoft.NumberFormat(dataRef.pembelian.terbayar,0)}}</div>
                        </div>
                    </div>
                    <div class="text-left border-t">
                        <div class="text-left flex-1">
                            <label for="">Type Pembayaran</label>
                            <select v-model="form.bank_id">
                                <option v-for="it in dataRef.bank" :value="it.id">{{it.nama_bank}}</option>
                            </select>
                        </div>
                        <div class="flex space-x-2">
                            <div class="text-left flex-1">
                                <label for="">Jumlah Setoran</label>
                                <input type="number" step="any" v-model="form.jumlah">
                            </div>
                            <div class="text-left flex-1">
                                <label for="">Tgl. Setoran</label>
                                <input type="datetime-local" v-model="form.tgl_setoran">
                            </div>
                        </div>
                        <div class="text-left">
                            <label for="">Penerima</label>
                            <input type="text" v-model="form.penerima">
                        </div>
                        <div>
                            <label for="">Catatan</label>
                            <textarea cols="30" rows="3" placeholder="Silakan isikan keterangan anda..!"
                                      v-model="form.catatan"></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2 pb-4 px-4 justify-end">
                    <button type="button" class="bg-basic-200 space-x-2" @click.prevent="dismisDialog">
                        <fa-icon icon="arrow-left"></fa-icon>
                        <span>Batal</span></button>
                    <button type="submit" @click.prevent="simpanSetoran" class="bg-basic-200 space-x-2">
                        <fa-icon icon="save"></fa-icon>
                        <span>Simpan</span></button>
                </div>
            </div>
        </div>
    </transition>
</template>
<script setup>
    import {ref, reactive, computed, onMounted, onActivated, watch} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';
    import {useRouter} from "vue-router";

    const dialog = new ref(false);
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const router = useRouter();
    const catatan = ref('');
    const dataRef = reactive({
        pembelian: {},
        bank: {}
    })
    const form = reactive({
        bank_id: 1,
        tgl_setoran: bsoft.DateFormat(Date.now(), "YYYY-MM-DDTHH:mm")
    });

    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        bsoft.axios.get(`/pembelian/${id}`).then(res => {
            if (res.data.code === 200) dataRef.pembelian = res.data.data;
        })
        bsoft.axios.get('/bank').then(res => {
            if (res.data.code === 200) dataRef.bank = res.data.data;
        })
    }

    function dismisDialog() {
        dialog.value = false;
        emit('onRefresh',dataRef.pembelian.id);
    }

    function simpanSetoran() {
        if(dataRef.pembelian.sisa_hutang<form.jumlah){
            Swal.fire({
                icon:'warning',
                title:'Peringatan...!',
                text:'Maaf jumlah uang melebih dari pembayaran sisa hutang..!'
            })
        }else{
            form.pembelian_id = dataRef.pembelian.id;
            bsoft.axios.post('/hutang/setoran', form).then(res => {
                if (res.data.code === 200) {
                    Object.entries(form).filter(q=>q[0]!='bank_id' && q[0]!='tgl_setoran').forEach(it=>{
                        form[it[0]]=null;
                    })
                    dismisDialog();
                }
            });
        }
    }

    defineExpose({
        showDialog, dismisDialog
    });
</script>
<style scoped>

</style>
