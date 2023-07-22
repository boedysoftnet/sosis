<template>
    <transition>
        <!--todo perhatikan saat pembelian retur artinya jika nilai minus system pembukuan accounting belum benar-->
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50 h-max-[450px]">
                <div class="py-2 px-5 font-semibold border-b flex items-center justify-between">
                    <h2>PEMBAYARAN</h2>
                    <h2>ID : {{data.pembelian.id}}</h2>
                </div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan">
                        <div class="border rounded shadow border-blue-200 p-2 mb-2  bg-blue-300 bg-opacity-10">
                            <div class="grid grid-cols-3">
                                <div>Supplier</div>
                                <div class="col-span-2 font-semibold">: {{data.pembelian.supplier.nama}}</div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Telpon</div>
                                <div class="col-span-2 font-semibold">: {{data.pembelian.supplier.telpon}}</div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Supplier</div>
                                <div class="col-span-2 font-semibold">: {{data.pembelian.supplier.nama}}</div>
                            </div>
                            <div class="grid grid-cols-3" v-if="data.pembelian.total_setelah_ppn>0">
                                <div>Status Hutang</div>
                                <div class="col-span-2 font-semibold flex items-center">
                                    <input type="checkbox" @change="checkStatusHutang()" class="mr-1" id="statusHutang"
                                           v-model="form.status_hutang">
                                    <label for="statusHutang" class="font-normal italic flex justify-between w-full">
                                        Bayar Dengan Tempo Waktu
                                        <b v-if="form.status_hutang">Terhutang : {{bsoft.NumberFormat(form.hutang)}}</b>
                                    </label>
                                </div>
                            </div>
                            <div v-if="form.status_hutang"
                                 class="border-t border-dashed border-blue-600 grid grid-cols-2 gap-2">
                                <div class="mt-2">
                                    <label>Tgl. Tempo</label>
                                    <input type="date" v-model="form.tgl_tempo"
                                           class="py-1 px-2 outline-none focus:border-basic-500 border w-full border-basic-200 w-full">
                                    <ValidateError :errors="errors" name="tgl_tempo"></ValidateError>
                                </div>
                                <div class="mt-2">
                                    <label>Catatan</label>
                                    <input type="text" v-model="form.catatan"
                                           class="py-1 px-2 outline-none focus:border-basic-500 border w-full border-basic-200 w-full">
                                    <ValidateError :errors="errors" name="catatan"></ValidateError>
                                </div>
                            </div>
                        </div>
                        <div class="border rounded shadow border-blue-200 p-2 mb-2 bg-yellow-300 bg-opacity-10">
                            <div class="grid grid-cols-3">
                                <div>Sub Total :</div>
                                <div class="col-span-2 font-semibold text-right">
                                    {{bsoft.NumberFormat(data.pembelian.sub_total)}}
                                </div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Diskon :</div>
                                <div class="col-span-2 font-semibold text-right">
                                    {{bsoft.NumberFormat(data.pembelian.diskon)}}
                                </div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Total :</div>
                                <div class="col-span-2 font-semibold text-right">
                                    {{bsoft.NumberFormat(data.pembelian.total)}}
                                </div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>PPN ({{data.pembelian.supplier.ppn}}%) :</div>
                                <div class="col-span-2 font-semibold text-right">
                                    {{bsoft.NumberFormat(data.pembelian.ppn)}}
                                </div>
                            </div>
                            <div class="grid grid-cols-3 font-semibold text-2xl mt-2">
                                <div>Total Bayar :</div>
                                <div class="col-span-2 font-semibold text-right">
                                    {{bsoft.NumberFormat(data.pembelian.total_setelah_ppn)}}
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label>Nomor Faktur</label>
                            <input type="text"
                                   class="py-1 px-2 outline-none focus:border-basic-500 w-full border border-basic-200 w-full"
                                   v-model="form.no_faktur" placeholder="EX: 10542"/>
                        </div>
                        <div class="mt-2">
                            <label>Type Pembayaran</label>
                            <select class="py-1 px-2 outline-none focus:border-basic-500 w-full border border-basic-200 w-full"
                                    v-model="form.bank_id" @change="changeBank()">
                                <option :value="it.id" v-for="it in data.bank">{{it.nama_bank}}</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="mt-2" :class="{'col-span-2':form.bank_id==1}">
                                <label>Uang Tunai</label>
                                <input type="number" ref="refUangTunai" step="any" @keyup="changeUangTunai()"
                                       v-model="form.uang_tunai"
                                       class="py-1 px-2 outline-none focus:border-basic-500 border w-full border-basic-200 w-full">
                                <ValidateError :errors="errors" name="uang_tunai"></ValidateError>
                            </div>
                            <div class="mt-2" v-if="form.bank_id!=1">
                                <label>Dana Transfer</label>
                                <input type="number" @keyup="changeDanaTransfer" step="any" v-model="form.dana_transfer"
                                       class="py-1 px-2 outline-none focus:border-basic-500 border w-full border-basic-200 w-full">
                                <ValidateError :errors="errors" name="dana_transfer"></ValidateError>
                            </div>
                        </div>
                        <div class="pt-3 text-right mt-2">
                            <button type="button" class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50"
                                    @click.prevent="dismisDialog"><span class="dx-icon-back "></span> Batal
                            </button>
                            <button class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50" type="submit" :disabled="pending"><span
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
    const form = reactive({
        uang_tunai: 0,
        bank_id: 1,
        dana_transfer: 0,
        status_hutang: false,
        hutang: 0
    });
    const pending = ref(false);
    const data = reactive({
        pembelian: {
            supplier: {}
        },
        bank: []
    });
    const refUangTunai = ref();
    const router = useRouter();

    function changeBank() {
        if (form.bank_id === 1) {
            form.uang_tunai = data.pembelian.total_setelah_ppn;
            form.dana_transfer = 0;
        } else {
            form.uang_tunai = 0;
            form.dana_transfer = data.pembelian.total_setelah_ppn;
        }
    }

    function checkStatusHutang() {
        if (!form.status_hutang) {
            form.tgl_tempo = null
        } else {
            form.hutang = data.pembelian.total_setelah_ppn;
            form.tgl_tempo = bsoft.DateFormat(Date.now() + ((3600 * 1000 * 24) * 7));
            form.uang_tunai = 0;
            form.dana_transfer = 0;
            refUangTunai.value.focus();
        }
    }

    function changeUangTunai() {
        if (form.bank_id != 1 && !form.status_hutang) {
            form.dana_transfer = data.pembelian.total_setelah_ppn - form.uang_tunai;
        } else {
            form.hutang = data.pembelian.total_setelah_ppn - (form.uang_tunai + form.dana_transfer);
        }
    }

    function changeDanaTransfer() {
        if (form.bank_id != 1 && !form.status_hutang) {
            form.uang_tunai = data.pembelian.total_setelah_ppn - form.dana_transfer;
        } else {
            form.hutang = data.pembelian.total_setelah_ppn - (form.uang_tunai + form.dana_transfer);
        }
    }


    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        Object.assign(form,{
            uang_tunai: 0,
            bank_id: 1,
            dana_transfer: 0,
            status_hutang: false,
            hutang: 0
        })
        bsoft.axios.get(`/pembelian/${id}`).then(res => {
            if (res.data.code === 200) {
                data.pembelian = res.data.data;
                form.uang_tunai = data.pembelian.total_setelah_ppn;
                setTimeout(() => {
                    refUangTunai.value.focus();
                }, 100)
            }
        })
        bsoft.axios.get(`/bank`).then(res => {
            if (res.data.code === 200) data.bank = res.data.data;
        });
    }

    function dismisDialog() {
        dialog.value = false;
        emit('onRefresh');
    }

    function sukses(res) {
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: res.message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            router.push({name: 'pembelian.daftar'})
        })
    }

    function setTypePembayaran() {
        if (form.bank_id === 1) {
            form.uang_tunai = data.pembelian.total_setelah_ppn;
        }
    }

    async function simpan() {
        pending.value = true;
        let uang = form.uang_tunai + form.dana_transfer;
        if (uang > data.pembelian.total_setelah_ppn) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan..!',
                text: 'Jumlah uang melebihi dari tagihan..!'
            });
        } else {
            bsoft.axios.post(`/pembelian/simpan/${data.pembelian.id}`, form).then(res => {
                if (res.data.code === 200) {
                    sukses(res.data);
                } else if (res.data.code = 201) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan..!',
                        text: res.data.message
                    }).then(() => {
                        pending.value = true;
                    })
                } else {
                    errors.value = res.data.data;
                }
            })
        }
    }


    defineExpose({
        showDialog,
    });
</script>
<style scoped>

</style>
