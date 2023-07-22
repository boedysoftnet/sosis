<template>
    <div>
        <h2 class="title font-semibold">DAFTAR HISTORY SETORAN HUTANG</h2>
    </div>
    <div class="grid-600">
        <div class="flex items-center space-x-2">
            <div class="grid grid-cols-2 gap-2">
                <div class="mb-2">
                    <label for="">Tgl. Transaksi</label>
                    <input type="date" class="w-full py-[3px] px-2 border outline-none border-basic-300"
                           @change="refresh()" v-model="form.tgl_awal"/>
                </div>
                <div class="mb-2">
                    <label for="">Sampai</label>
                    <input type="date" class="w-full py-[3px] px-2 border outline-none border-basic-300"
                           @change="refresh()" v-model="form.tgl_akhir"/>
                </div>
            </div>
        </div>

        <dx-data-grid
                :allow-column-reordering="true"
                :allow-column-resizing="true"
                :column-auto-width="true"
                :show-borders="true"
                :focused-row-enabled="true"
                :hover-state-enabled="true"
                :data-source="dataRef.dataSource">
            <dx-column data-field="id" />
            <dx-column data-field="unit_setor" />
            <dx-column data-field="tgl_setoran" />
            <dx-column data-field="nama" />
            <dx-column data-field="alamat" />
            <dx-column data-field="telpon" />
            <dx-column data-field="jumlah" />
            <dx-column data-field="penerima" />
            <dx-column data-field="catatan" />
            <dx-column cell-template="cellPrint" caption="#"/>
            <template #cellPrint="{data:row}">
                <a href="" @click.prevent="dialogBuktiSetoran.showPrint(row.data.id)"><fa-icon icon="print"/></a>
            </template>
            <dx-filter-row :visible="true"/>
            <dx-paging enabled="false"/>
        </dx-data-grid>
        <pembelian-print-bukti-setoran class="hidden" ref="dialogBuktiSetoran"/>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging, DxSummary, DxTotalItem} from 'devextreme-vue/data-grid';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";
    import Swal from "sweetalert2";
    import PembelianPrintBuktiSetoran from './PembelianPrintBuktiSetoran.vue';

    const bsoft = useBoedysoft();
    const dialogRincian = ref();
    const dialogBuktiSetoran = ref();
    const dataRef = reactive({
        dataSource: null
    });
    const form = reactive({
        tgl_awal: bsoft.FirstDate(Date.now()),
        tgl_akhir: bsoft.LastDate(Date.now()),
    })

    function refresh() {
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get('/pembelian/piutang/history', {params: form});
                if (data.code === 200) return data.data;
            }
        })
    }

    onMounted(refresh);
</script>

<style scoped>

</style>
