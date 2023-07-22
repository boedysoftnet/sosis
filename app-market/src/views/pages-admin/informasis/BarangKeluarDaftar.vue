<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
        <h2 class="title mb-5 uppercase">Item Barang Keluar</h2>
            <a href="" @click.prevent="refresh">Refresh</a>
        </div>
        <div>
            <div class="flex space-x-2 mb-4">
                <div class="flex-1">
                    <label for="">Tampilkan Periode</label>
                    <input type="date" v-model="form.tgl_awal" class="py-1" @change="refresh">
                </div>
                <div class="flex-1">
                    <label for="">S/D</label>
                    <input type="date" v-model="form.tgl_akhir" class="py-1" @change="refresh">
                </div>
            </div>
        </div>
        <div class="grid-500">
            <dx-data-grid
                    :allow-column-reordering="true"
                    :allow-column-resizing="true"
                    :column-auto-width="true"
                    :show-borders="true"
                    :focused-row-enabled="true"
                    :data-source="dataSource">
                <dx-column data-field="id"/>
                <dx-column data-field="nama_barang"/>
                <dx-column data-field="kategori"/>
                <dx-column data-field="harga" data-type="number" format="#,##0"/>
                <dx-column data-field="jumlah" data-type="number" format="#,##0"/>
                <dx-column data-field="satuan" width="70"/>
                <dx-column data-field="total" data-type="number" format="#,##0"/>
                <dx-summary>
                    <dx-total-item column="total" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                    <dx-total-item column="jumlah" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                </dx-summary>
                <dx-filter-row :visible="true"/>
                <dx-paging :enabled="false"/>
            </dx-data-grid>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue'
    import {DxDataGrid, DxColumn, DxTotalItem,DxPaging, DxSummary, DxFilterRow} from 'devextreme-vue/data-grid'
    import {useBoedysoft} from "../../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";

    const bsoft = useBoedysoft();
    const dataRef = reactive({
        dataSource: null
    })
    const dataSource=ref();
    const form=reactive({
        tgl_awal: bsoft.DateFormat(Date.now()),
        tgl_akhir: bsoft.DateFormat(Date.now())
    })

    async function refresh() {
        dataSource.value = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get('/penjualan/item-terjual',{params:form});
                return data.data;
            }
        });
    }

    onMounted(refresh)


</script>

<style scoped>

</style>
