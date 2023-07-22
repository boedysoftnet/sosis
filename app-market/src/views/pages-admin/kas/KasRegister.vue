<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50 h-max-[450px]">
                <div class="py-2 px-5 font-semibold border-b flex items-center justify-between">
                    <h2>REGISTER KAS</h2>
                </div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan">
                        <div class="grid grid-cols-2 gap-2">
                            <div class="mt-2">
                                <label>Kategori Transaksi</label>
                                <select class="py-1 px-2 outline-none focus:border-basic-500 w-full border border-basic-200 w-full"
                                        v-model="form.akun_perkiraan_id">
                                    <option value="" selected disabled class="text-sm">[ Pilih Kategori ]</option>
                                    <option :value="it.id" v-for="it in data.akun_perkiraan">{{it.nama_akun}}</option>
                                </select>
                            </div>
                            <div class="mt-2">
                                <label>Tanggal</label>
                                <input type="datetime-local"
                                       class="py-1 px-2 outline-none focus:border-basic-500 w-full border border-basic-200 w-full"
                                       v-model="form.created_at"/>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label>Type Pembayaran</label>
                            <select class="py-1 px-2 outline-none focus:border-basic-500 w-full border border-basic-200 w-full"
                                    v-model="form.bank_id">
                                <option :value="it.id" v-for="it in data.bank">{{it.nama_bank}}</option>
                            </select>
                        </div>
                        <div class="mt-2">
                            <label class="flex justify-between items-center">
                                <p>Nominal</p>
                            </label>
                            <div class="flex items-center space-x-2">
                                <input type="number" step="any" @keyup="changeUangTunai()"
                                       v-model="form.nominal"
                                       class="py-1 px-2 outline-none focus:border-basic-500 border w-full border-basic-200 w-full text-right">
                                <p class="font-semibold pl-2">Rp.{{bsoft.NumberFormat(form.nominal)}}</p>
                            </div>
                            <ValidateError :errors="errors" name="uang_tunai"></ValidateError>
                        </div>
                        <div class="mt-2">
                            <label>Catatan</label>
                            <textarea v-model="form.catatan" placeholder="Berikan catatan singkat dan jelas..!"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border w-full border-basic-200 w-full">
                            </textarea>
                            <ValidateError :errors="errors" name="uang_tunai"></ValidateError>
                        </div>
                        <div class="pt-3 text-right mt-2">
                            <button type="button" class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50"
                                    @click.prevent="dismisDialog"><span class="dx-icon-back "></span> Batal
                            </button>
                            <button class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50" type="submit"
                                    :disabled="pending"><span
                                    class="dx-icon-save"></span> Simpan
                            </button>
                        </div>
                    </form>
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
    const meta = {
        akun_perkiraan_id: '',
        catatan: null,
        nominal: 0,
        bank_id: 1,
    }
    const form = reactive({...meta});
    const pending = ref(false);
    const data = reactive({
        penjualan: {
            customer: {}
        },
        bank: [],
        akun_perkiraan:[]
    });
    const router = useRouter();

    function resetForm() {
        Object.assign(form, meta);
    }

    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        pending.value = false;
        resetForm();
        form.created_at = bsoft.DateFormat(Date.now(), 'YYYY-MM-DDTHH:mm');
        bsoft.axios.get(`/bank`).then(res => {
            if (res.data.code === 200) data.bank = res.data.data;
        });
        bsoft.axios.get(`/akun-perkiran/show-transaksi`).then(res => {
            if (res.data.code === 200) data.akun_perkiraan = res.data.data;
        });
    }

    function dismisDialog() {
        dialog.value = false;
        console.log('Halo bro');
        emit('onRefresh');
    }

    async function sukses(res) {
        await Swal.fire({
            position: 'center',
            icon: 'success',
            title: res.message,
            showConfirmButton: false,
            timer: 1500
        });
        pending.value = false;
        dismisDialog();
    }

    async function simpan() {
        if(form.nominal<=0){
            Swal.fire({
                icon:"warning",
                title:"Maaf Nominal tidak falid..!"
            })
        }else{
            kirim();
        }
    }

    function kirim() {
        bsoft.axios.post(`/kas`, form).then(res => {
            if (res.data.code === 200) {
                sukses(res.data);
            } else if (res.data.code = 201) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan..!',
                    text: res.data.message
                }).then(() => {
                    pending.value = false;
                })
            } else {
                errors.value = res.data.data;
                pending.value = false;
            }
        })
    }


    defineExpose({
        showDialog,
    });
</script>
<style scoped>

</style>
