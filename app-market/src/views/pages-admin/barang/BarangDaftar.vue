<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
            <div class="title">Daftar Barang</div>
            <div class="space-x-5 flex">
                <a href="" class="flex items-center space-x-1" @click.prevent="importBarang.showDialog()"><span class="dx-icon-import"></span> <span>Import</span></a>
                <a href="" class="flex items-center space-x-1" @click.prevent="register.showDialog()"><span class="dx-icon-plus"></span> <span>Tambah Barang</span></a>
            </div>
        </div>
        <div class="mt-5 grid-600">
            <DxDataGrid
                    :data-source="dataSource"
                    :allow-column-reordering="true"
                    :allow-column-resizing="true"
                    :column-auto-width="true"
                    :show-borders="true"
                    :focused-row-enabled="true"
                    :hover-state-enabled="true">
                <DxColumn data-field="nama_barang"/>
                <DxColumn data-field="barang_kategori.kategori"/>
                <dx-column cell-template="cellStatus" caption="Status" width="50"/>
                <DxColumn data-field="pokok" data-type="number" format="#,###"/>
                <DxColumn data-field="harga_jual" data-type="number" format="#,###" caption="Jual"/>
                <DxColumn data-field="satuan" caption="Satuan"/>
                <template #cellStatus="{data:row}">
                    <div v-if="parseFloat(row.data.pokok)>parseFloat(row.data.harga_jual)" class="w-5 h-5 bg-red-500 rounded-full shadow mx-auto" title="Harga tidak wajar, mohon di check..!"></div>
                    <div v-if="parseFloat(row.data.pokok)<parseFloat(row.data.harga_jual)" class="w-5 h-5 bg-green-500 rounded-full shadow mx-auto" title="Harga Normal"></div>
                    <div v-if="parseFloat(row.data.pokok)===parseFloat(row.data.harga_jual)" class="w-5 h-5 bg-yellow-500 rounded-full shadow mx-auto" title="Harga Pokok dan harga Jual sama..!"></div>
                </template>
                <DxColumn data-field="sisa_stock" caption="Stock" format="#,##0.0"/>
                <DxColumn data-field="created_at" data-type="date" format="dd MMM yyyy"/>
                <DxColumn data-field="deskripsi"/>
                <DxColumn cell-template="action" data-field="id"/>
                <template #action="{data}">
                    <div class="flex items-center justify-around">
                        <a href="" @click.prevent="edit(data.value)"><span class="dx-icon-edit"></span></a>
                        <a href="" @click.prevent="destroy(data.value)"><span class="dx-icon-remove"></span></a>
                    </div>
                </template>
                <DxFilterRow :visible="true"></DxFilterRow>
                <dx-paging :enabled="true"/>
                <dx-summary>
                  <dx-total-item column="nama_barang" display-format="Total" value-format="#,##0"/>
                  <dx-total-item column="sisa_stock" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                  <dx-total-item column="satuan" summary-type="count" display-format="{0}" value-format="#,##0"/>
                </dx-summary>
            </DxDataGrid>
        </div>
        <BarangRegister ref="register" @onRefresh="refresh()"></BarangRegister>
        <BarangImport ref="importBarang" @onRefresh="refresh()"></BarangImport>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import BarangRegister from './BarangRegister.vue';
    import BarangImport from './BarangImport.vue';
    import {DxDataGrid, DxColumn, DxFilterRow,DxPaging,DxSummary,DxTotalItem} from "devextreme-vue/data-grid";
    import CustomStore from "devextreme/data/custom_store";
    import Swal from 'sweetalert2';

    const register = ref();
    const importBarang = ref();
    const table = ref();
    const dataSource = ref(null);


    function edit(id) {
        register.value.showDialog(id);
    }

    async function refresh() {
        dataSource.value = new CustomStore({
            "key": 'id',
            "load": async () => {
                const {data} = await useBoedysoft().axios.get('/barang');
                return data.data;
            },
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
                useBoedysoft().axios.delete('/barang/' + id);
                refresh();
            }
        })
    }

    onMounted(refresh);

</script>

<style scoped>

</style>
