<template>
    <div class="boedysoft">
        <h2 class="title mb-5">Barang Expaid</h2>
        <div>
            <div class="flex space-x-2 mb-4">
                <div class="flex-1">
                    <label for="">Tampilkan data Expaid</label>
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
                    :data-source="dataRef.dataSource">
                <dx-column data-field="nama_barang" />
                <dx-column data-field="supplier" :group-index="0"/>
                <dx-column data-field="expaid"/>
                <dx-column data-field="kategori" :group-index="1"/>
                <dx-column data-field="masa"/>
                <dx-column data-field="qty" data-type="number"/>
                <dx-column data-field="satuan"/>
                <dx-column data-field="total" format='#,##0'/>
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
    const form=reactive({
        tgl_awal: bsoft.FirstDate(Date.now()),
        tgl_akhir: bsoft.LastDate(Date.now())
    })

    async function refresh() {
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get('/pembelian/informasi/expaid',{params:form});
                return data.data;
            }
        });
    }

    onMounted(refresh)


</script>

<style scoped>

</style>
