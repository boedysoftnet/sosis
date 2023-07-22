<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
            <div class="title">Daftar Item Barang Masuk</div>
        </div>
        <div class="mt-5 grid-500">
            <div class="my-2 grid grid-cols-2 gap-2">
                <div>
                    <label for="">Periode </label>
                    <input type="date" v-model="form.tgl_awal" @change="refresh"
                           class="py-1 px-2 border border-basic-300 outline-none w-full">
                </div>
                <div>
                    <label for="">Sampai </label>
                    <input type="date" v-model="form.tgl_akhir" @change="refresh"
                           class="py-1 px-2 border border-basic-300 outline-none w-full">
                </div>
            </div>
            <dx-data-grid
                    :data-source="dataSource"
                    :allow-column-reordering="true"
                    :allow-column-resizing="true"
                    :column-auto-width="true"
                    :show-borders="true"
                    :focused-row-enabled="true"
                    :hover-state-enabled="true">
                <dx-column data-field="nama_barang"/>
                <dx-column data-field="barang_kategori.kategori" :group-index="0"/>
                <dx-column data-field="harga_terakhir" caption="HPP Akhir" data-type="number" format="#,##0"/>
<!--                <dx-column data-field="harga_jual" data-type="number" format="#,##0" caption="Jual"/>-->
                <dx-column data-field="stock_masuk" caption="Masuk" format="#,##0"/>
                <dx-column data-field="satuan" caption="Satuan"/>
                <dx-column data-field="total_pokok" caption="Nilai Pokok" data-type="number" format="#,##0"/>
                <dx-column cell-template="action" caption="Opsi"/>
                <template #action="{data:row}">
                    <div class="flex items-center justify-around">
                        <a href="" @click.prevent="showDialog(row.data.id)"><span
                                class="dx-icon-folder"></span></a>
                    </div>
                </template>
                <dx-filter-row :visible="true"></dx-filter-row>
                <dx-summary>
                    <dx-total-item column="total_pokok" summary-type="sum" alignment="right" value-format="#,###"
                                   display-format="{0}"/>
                </dx-summary>
                <dx-paging :enabled="false"/>
            </dx-data-grid>
        </div>
        <barang-detail-masuk ref="refDetailMasuk"></barang-detail-masuk>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import {DxDataGrid, DxColumn, DxFilterRow, DxSummary, DxTotalItem,DxPaging} from "devextreme-vue/data-grid";
    import CustomStore from "devextreme/data/custom_store";
    import Swal from 'sweetalert2';
    import barangDetailMasuk from './BarangDetailMasuk.vue';

    const dataSource = ref(null);
    const form = reactive({
        tgl_awal: null,
        tgl_akhir: null
    })
    const bsoft = useBoedysoft();
    const refDetailMasuk = ref();

    function showDialog(id) {
        bsoft.axios.get(`pembelian/barang/detail/${id}`, {params: form}).then(res => {
            if (res.data.code === 200 && res.data.data.length>0) {
                refDetailMasuk.value.showDialog(res.data.data);
            }else{
                Swal.fire({
                    icon:'warning',
                    title:'Ops...!',
                    text: 'Maaf data tidak ada mohon check kembali..!'
                });
            }
        })
    }

    async function refresh() {
        dataSource.value = new CustomStore({
            "key": 'id',
            "load": async () => {
                const {data} = await useBoedysoft().axios.get('/pembelian/barang/masuk', {params: form});
                return data.data;
            },
        })
    }

    onMounted(() => {
        form.tgl_awal = bsoft.DateFormat(Date.now());
        form.tgl_akhir = bsoft.LastDate(Date.now());
        refresh();
    });

</script>

<style scoped>

</style>
