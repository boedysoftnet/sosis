<template>
    <div class="boedysoft">
        <div class="p-5 border border-gray-300 shadow my-5 print:px-20" v-if="akunPerkiraan" id="printMe">
            <div class="flex flex-col items-center text-center" v-if="perusahaan">
                <img :src="perusahaan.icon" alt="" class="w-32">
                <div class="text-center  w-full">
                    <h2 class="font-semibold text-2xl">{{perusahaan.nama}}</h2>
                    <div class="flex space-x-2 items-center justify-center">
                        <p class="text-sm">{{perusahaan.alamat}}</p>
                        <p>Email : {{perusahaan.email}}</p>
                        <p>Phone : {{perusahaan.telpon}}</p>
                    </div>
                </div>
            </div>
            <div class="font-semibold text-center">
                <h2 class="text-2xl border-b py-4">AKUMULASI RUGI-LABA</h2>
            </div>
            <div class="p-3">
                <template v-for="(its,golongan) in bsoft.groupBy(akunPerkiraan,'golongan')">
                    <div class="font-semibold uppercase mt-3">{{golongan.substring(2)}}</div>
                    <template v-for="(items,kelompok) in bsoft.groupBy(its,'kelompok')">
                        <div class="ml-5">
                            <div>{{kelompok.substring(2)}}</div>
                            <div class="ml-5">
                                <template v-for="it in items">
                                    <div class="flex">
                                        <div> {{it.kode_akun}} : {{it.nama_akun}}</div>
                                        <div class="border-b border-dashed flex-1 border-basic-300"></div>
                                        <div class="text-right font-semibold mr-32">
                                            {{bsoft.NumberFormat((it.kredit-it.debet).toFixed(0))}}
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="flex justify-between">
                                <div class="ml-5 uppercase font-semibold">Total {{kelompok.substring(2)}}</div>
                                <div class="font-semibold text-xl border-b-2 border-double border-basic-400">
                                    {{bsoft.NumberFormat(items.reduce((n,{debet,kredit})=>n+(kredit-debet),0).toFixed(0))}}
                                </div>
                            </div>
                        </div>
                    </template>
                </template>
                <div class="text-right mt-2">
                    <div class="font-semibold text-2xl">
                        {{bsoft.NumberFormat(akunPerkiraan.reduce((n,{debet,kredit})=>n+(kredit-debet),0).toFixed(0))}}
                    </div>
                </div>
                <div class="print:hidden flex items-end space-x-2 border-t mt-2 border-basic-300 pt-3">
                    <div class="flex space-x-2">
                        <div>
                            <label for="">Periode</label>
                            <input type="date" v-model="form.tgl_awal" @change="refresh">
                        </div>
                        <div>
                            <label for="">S/D</label>
                            <input type="date" v-model="form.tgl_akhir" @change="refresh">
                        </div>
                    </div>
                    <button @click.prevent="printDialog" class="border-none bg-basic-200 shadow"><span
                            class="fa fa-print"></span> Print
                    </button>
                </div>
            </div>
        </div>
        <arus-pembukuan ref="dialogArus"/>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import {
        DxPaging,
        DxDataGrid,
        DxColumn,
        DxFilterRow,
        DxScrolling,
        DxSummary,
        DxTotalItem
    } from "devextreme-vue/data-grid";
    import CustomStore from "devextreme/data/custom_store";
    import Swal from 'sweetalert2';
    import ArusPembukuan from './ArusPembukuan.vue';

    const register = ref();
    const table = ref();
    const akunPerkiraan = ref(null);
    const periode = ref();
    const bsoft = useBoedysoft();
    const dialogArus = ref();
    const perusahaan = ref();
    const form = reactive({
        tgl_awal: bsoft.FirstDate(Date.now()),
        tgl_akhir: bsoft.LastDate(Date.now()),
    })


    function printDialog() {
        setTimeout(() => bsoft.printPeper('printMe'), 1000);
    }

    function edit(id) {
        register.value.showDialog(id);
    }

    async function refresh() {
        perusahaan.value=await bsoft.profilePerusahaan();
        bsoft.axios.get('/rugi-laba', {params: form}).then(res => {
            if (res.data.code === 200) {
                akunPerkiraan.value = res.data.data;
            }
        })
    }

    function destroy(id) {
        Swal.fire({
            title: 'Hapus Data ini ?',
            text: "Anda akan menghapus data secara permanent, Lanjutkan..?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus sekarang!',
        }).then((result) => {
            if (result.isConfirmed) {
                useBoedysoft().axios.delete('/akun-perkiraan/' + id);
                refresh();
            }
        })
    }

    function resetDatabase() {
        bsoft.axios.post('/reset-database').then(res => {
            if (res.data.code === 200) Swal.fire({
                icon: "success",
                title: 'Reset Data Berhasil..!',
                text: res.data.message
            }).then(() => refresh());
        })
    }

    onMounted(() => {
        periode.value = bsoft.LastDate(Date.now());
        refresh();
    });


</script>

<style scoped>
    #gridContainer {
        height: 740px !important;
    }
</style>
