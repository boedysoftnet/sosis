<template>
    <div>
        <div class="mb-3">
            <h2 class="font-semibold flex flex-col">Stock Opname Perushaaan : <span
                    class="font-semibold drop-shadow text-basic-600 text-2xl">UNIT : {{dataRef.perusahaan.nama}}</span>
            </h2>
        </div>
        <div>
            <form @submit.prevent="checkOpnameStock"
                  class="flex space-x-2 w-full p-3 bg-basic-600 bg-opacity-10 rounded shadow mb-3">
                <div class="w-32">
                    <label for="">Kode</label>
                    <div class="flex">
                        <input type="text" step="any" v-model="form.id" readonly class="bg-gray-200">
                        <button type="button" class="border-none bg-basic-500 text-white outline-none"
                                @click.prevent="dialogBarang.showDialog">
                            <fa-icon icon="folder-plus"></fa-icon>
                        </button>
                    </div>
                </div>
                <pilih-barang ref="dialogBarang" @setBarang="setBarang"></pilih-barang>
                <stock-opname-catatan ref="dialogCatatan" @onSave="simpan"></stock-opname-catatan>
                <div class="flex-1">
                    <label for="">Nama Barang</label>
                    <input type="text" v-model="form.nama_barang" readonly class="bg-gray-200">
                </div>
                <div class="w-32">
                    <label for="">Stock</label>
                    <input type="text" v-model="form.sisa_stock" readonly class="bg-gray-200">
                </div>
                <div class="w-32">
                    <label for="">Satuan</label>
                    <input type="text" v-model="form.satuan" readonly class="bg-gray-200">
                </div>
                <div class="w-32">
                    <label for="">Jumlah</label>
                    <div class="flex">
                        <input type="number" step="any" ref="jumlahRef" v-model="form.qty"
                               title="NB: Jumlah merupakan nilai akhir stock fisik yang tersedia, system otomatis akan menyesuaikan dengan jumlah nilai akhir yang anda inputkan..!">
                        <button class="border-none bg-basic-500 text-white outline-none">
                            <fa-icon icon="plus"></fa-icon>
                        </button>
                    </div>
                </div>
            </form>
            <div class="flex space-x-2 w-full p-3 bg-basic-600 bg-opacity-10 rounded shadow">
                <dx-data-grid
                        :allow-column-resizing="true"
                        :column-auto-width="true"
                        :show-borders="true"
                        :focused-row-enabled="true"
                        :data-source="dataRef.dataSource">
                    <dx-column data-field="nama_barang"/>
                    <dx-column data-field="harga" format="#,##0"/>
                    <dx-column data-field="stock_sebelumnya" format="#,##0"/>
                    <dx-column data-field="qty" format="#,##0"/>
                    <dx-column data-field="stock_akhir" format="#,##0"/>
                    <dx-column data-field="satuan"/>
                    <dx-column data-field="total" format="#,##0"/>
                    <dx-column data-field="alasan"/>
                    <dx-filter-row :visible="true"/>
                    <dx-summary>
                        <dx-total-item column="total" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                    </dx-summary>
                    <dx-paging :enabled="false"/>
                </dx-data-grid>
            </div>
            <div class="flex space-x-2 w-1/5 mt-3">
                <div class="flex-1">
                    <label for="">Filter Periode </label>
                    <input type="date" v-model="filter.tgl_awal" @change="refresh">
                </div>
                <div class="flex-1">
                    <label for="">S/D</label>
                    <div class="flex">
                        <input type="date" v-model="filter.tgl_akhir" @change="refresh">
                        <button class="border bg-basic-300 flex items-center space-x-2" @click.prevent="dialog.showPopup">
                            <fa-icon icon="print"></fa-icon>
                            <span>Print</span>
                            <popover ref="dialog"/>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging, DxSummary, DxTotalItem} from 'devextreme-vue/data-grid'
    import CustomStore from "devextreme/data/custom_store";
    import pilihBarang from '../../../components/pages-admin/PilihBarang.vue';
    import Swal from 'sweetalert2';
    import stockOpnameCatatan from './StockOpnameCatatan.vue';
    import popover from '../../../components/pages-admin/Popover.vue'

    const bsoft = useBoedysoft();
    const dataRef = reactive({
        perusahaan: {},
        dataSource: null,
    });
    const form = reactive({});
    const dialogBarang = ref();
    const jumlahRef = ref();
    const filter = reactive({
        tgl_awal: bsoft.DateFormat(Date.now()),
        tgl_akhir: bsoft.DateFormat(Date.now()),
    })
    const dialogCatatan = ref();
    const dialog=ref();

    function refresh() {
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get('/stock/opname', {params: filter});
                return data.data;
            }
        })
    }

    function init() {
        bsoft.axios.get('/user/profile').then(res => {
            if (res.data.code === 200) {
                dataRef.perusahaan = res.data.data.perusahaan;
            }
        })
    }

    function setBarang(id) {
        bsoft.axios.get(`/barang/${id}`).then(res => {
            if (res.data.code === 200) {
                Object.assign(form, res.data.data);
            }
            jumlahRef.value.focus();
        });
    }

    function checkOpnameStock() {
        if(form.qty!=null){
            let sisa = form.qty - form.sisa_stock;
            if (sisa > 0) {
                simpan();
            } else {
                dialogCatatan.value.showDialog();
            }
        }else{
            Swal.fire('Maaf anda belum menentukan qty..!');
        }
    }

    function simpan(catatan = '-') {
        if (form.qty >=0) {
            form.alasan = catatan;
            bsoft.axios.post('/stock/opname', form).then(res => {
                if (res.data.code === 200) {
                    dialogCatatan.value.dismisDialog();
                    Swal.fire({
                        icon: 'success',
                        title: 'Opname Berhasill!',
                        text: res.data.message,
                        timer: 1000,
                        showCancelButton: false,
                        showConfirmButton: false,
                    }).then(() => refresh());
                    Object.assign(form, {
                        'id': null,
                        'nama_barang': null,
                        'satuan': null,
                        'qty': null,
                        'sisa_stock': null,
                    });
                }
            })
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Anda belum memilih / menentukan jumlah ..!',
                showCancelButton: false,
            });
        }
    }

    onMounted(() => {
        refresh();
        init();
    })


</script>

<style >
    .dx-gridbase-container {
        height: 540px;
    }
</style>
