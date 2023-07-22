<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 overflow-auto z-10 flex items-center justify-center">
            <div class="w-3/4 xl:w-1/2 shadow bg-basic-50 xl:h-max-[450px]">
                <div class="py-2 px-5 font-semibold border-b flex items-center justify-between">
                    <h2>PEMBAYARAN</h2>
                    <h2>ID : {{data.penjualan.id}}</h2>
                </div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan">
                        <div class="border rounded shadow border-blue-200 p-2 mb-2  bg-blue-300 bg-opacity-10">
                            <div class="grid grid-cols-3">
                                <div>Supplier</div>
                                <div class="col-span-2 font-semibold">: {{data.penjualan.customer.nama}}</div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Telpon</div>
                                <div class="col-span-2 font-semibold">: {{data.penjualan.customer.telpon}}</div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Supplier</div>
                                <div class="col-span-2 font-semibold">: {{data.penjualan.customer.nama}}</div>
                            </div>
                            <div class="grid grid-cols-3" v-if="data.penjualan.total_setelah_ppn>0">
                                <div>Status Hutang</div>
                                <div class="col-span-2 font-semibold flex items-center">
                                    <input type="checkbox" @change="checkStatusPiutang()" class="mr-1" id="statusHutang"
                                           v-model="form.status_piutang">
                                    <label for="statusHutang" class="font-normal italic flex justify-between w-full">
                                        Bayar Dengan Tempo Waktu
                                        <b v-if="form.status_piutang">Terpiutang :
                                            {{bsoft.NumberFormat(form.piutang)}}</b>
                                    </label>
                                </div>
                            </div>
                            <div v-if="form.status_piutang"
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
                                    {{bsoft.NumberFormat(data.penjualan.sub_total)}}
                                </div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Diskon :</div>
                                <div class="col-span-2 font-semibold text-right">
                                    {{bsoft.NumberFormat(data.penjualan.diskon)}}
                                </div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>Total :</div>
                                <div class="col-span-2 font-semibold text-right">
                                    {{bsoft.NumberFormat(data.penjualan.total)}}
                                </div>
                            </div>
                            <div class="grid grid-cols-3">
                                <div>PPN ({{data.penjualan.customer.ppn}}%) :</div>
                                <div class="col-span-2 font-semibold text-right">
                                    {{bsoft.NumberFormat(data.penjualan.ppn)}}
                                </div>
                            </div>
                            <div class="grid grid-cols-3 font-semibold text-2xl mt-2">
                                <div>Total Bayar :</div>
                                <div class="col-span-2 font-semibold text-right">
                                    {{bsoft.NumberFormat(data.penjualan.total_setelah_ppn)}}
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label>Tanggal</label>
                            <input type="datetime-local"
                                   class="py-1 px-2 outline-none focus:border-basic-500 w-full border border-basic-200 w-full"
                                   v-model="form.created_at"/>
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
                        <div class="mt-2 text-right p-3 bg-green-500 rounded">
                            <label class="font-semibold">Kembalian</label>
                            <h2 class="text-2xl font-semibold">Rp.{{bsoft.NumberFormat(form.kembalian)}}</h2>
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
<!--                    <penjualan-print-nota class="hidden" @onFinish="dismisDialog" ref="dialogPrint"/>-->
                    <penjualan-print-nota-kecil class="hidden" @onFinish="dismisDialog" ref="dialogPrint"/>
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
    import PenjualanPrintNota from './PenjualanPrintNota.vue'
    import PenjualanPrintNotaKecil from './PenjualanPrintNotaKecil.vue'

    const dialog = new ref(false);
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const form = reactive({
        uang_tunai: 0,
        bank_id: 1,
        dana_transfer: 0,
        status_piutang: false,
        piutang: 0,
        kembalian: 0,
        created_at: bsoft.DateFormat(Date.now(), 'YYYY-MM-DDTHH:mm')
    });
    const pending = ref(false);
    const data = reactive({
        penjualan: {
            customer: {}
        },
        bank: []
    });
    const refUangTunai = ref();
    const router = useRouter();
    const dialogPrint =ref();

    function changeBank() {
        if (form.bank_id === 1) {
            form.uang_tunai = data.penjualan.total_setelah_ppn;
            form.dana_transfer = 0;
        } else {
            form.uang_tunai = 0;
            form.dana_transfer = data.penjualan.total_setelah_ppn;
        }
    }

    function checkStatusPiutang() {
        if (!form.status_piutang) {
            form.tgl_tempo = null
        } else {
            form.piutang = data.penjualan.total_setelah_ppn;
            form.tgl_tempo = bsoft.DateFormat(Date.now() + ((3600 * 1000 * 24) * 7));
            form.uang_tunai = 0;
            form.dana_transfer = 0;
            refUangTunai.value.focus();
        }
        kembalian();
    }

    function changeUangTunai() {
        if (form.bank_id != 1 && !form.status_piutang) {
            form.dana_transfer = data.penjualan.total_setelah_ppn - form.uang_tunai;
        } else {
            form.piutang = data.penjualan.total_setelah_ppn - (form.uang_tunai + form.dana_transfer);
        }
        kembalian();
    }

    function kembalian() {
        let jumlah = form.uang_tunai + form.dana_transfer;
        form.kembalian = jumlah - data.penjualan.total_setelah_ppn;
        console.log(form.kembalian)
        if (form.kembalian < 0) form.kembalian = 0;
    }

    function changeDanaTransfer() {
        if (form.bank_id != 1 && !form.status_piutang) {
            form.uang_tunai = data.penjualan.total_setelah_ppn - form.dana_transfer;
        } else {
            form.piutang = data.penjualan.total_setelah_ppn - (form.uang_tunai + form.dana_transfer);
        }
        kembalian();
    }

    function resetForm() {
        Object.assign(form, {
            uang_tunai: 0,
            bank_id: 1,
            dana_transfer: 0,
            status_piutang: false,
            piutang: 0,
            kembalian: 0,
            created_at: bsoft.DateFormat(Date.now(), 'YYYY-MM-DDTHH:mm')
        });
    }

    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        pending.value=false;
        resetForm();
        form.created_at = bsoft.DateFormat(Date.now(), 'YYYY-MM-DDTHH:mm');
        bsoft.axios.get(`/penjualan/${id}`).then(res => {
            if (res.data.code === 200) {
                data.penjualan = res.data.data;
                form.uang_tunai = data.penjualan.total_setelah_ppn;
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
        dialogPrint.value.showPrint(res.data.id);
    }

    function setTypePembayaran() {
        if (form.bank_id === 1) {
            form.uang_tunai = data.penjualan.total_setelah_ppn;
        }
    }

    async function simpan() {
        pending.value = true;
        let uang = form.uang_tunai + form.dana_transfer;
        if (uang < data.penjualan.total_setelah_ppn && form.status_piutang == false) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan..!',
                text: 'Jumlah uang tidak mencukupi..!'
            });
        } else {
            if (form.status_piutang && uang > data.penjualan.total_setelah_ppn) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan..!',
                    text: 'Maaf jumlah uang lebih besar dari jumlah tagihan..!'
                });
            } else {
                kirim();
            }
        }
    }

    function kirim() {
        bsoft.axios.post(`/penjualan/simpan/${data.penjualan.id}`, form).then(res => {
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
            }
        })
    }


    defineExpose({
        showDialog,
    });
</script>
<style scoped>

</style>
