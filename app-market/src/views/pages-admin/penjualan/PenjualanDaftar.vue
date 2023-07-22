<template>
    <div>
        <h2 class="title font-semibold">DAFTAR PENJUALAN</h2>
    </div>
    <div class="grid-600">
        <div class="flex items-center space-x-2">
            <div class="mb-2">
                <label for="">Type Transaksi</label>
                <select class="w-full py-1 px-2 border outline-none border-basic-300" v-model="form.type_transaksi"
                        @change="refresh()">
                    <option value="Preorder">Preorder</option>
                    <option value="Khusus Terhutang">Khusus Terhutang</option>
                    <option value="Finish">Selesai</option>
                    <option value="">Semua</option>
                </select>
            </div>
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
            <div class="flex items-center pt-2">
                <a href="" @click.prevent="refresh" class="space-x-1"><span class="fa fa-refresh"></span> <span>Refresh</span></a>
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
            <dx-column data-field="id"/>
            <dx-column cell-template="cellCustomer" caption="Customer"/>
            <template #cellCustomer="{data:row}">
                <a href="" class="font-semibold flex items-center justify-between"
                   @click.prevent="dialogRincian.showDialog(row.data.id)"><span>{{row.data.customer}}</span>
                    <fa-icon icon="arrow-right"></fa-icon>
                </a>
            </template>
            <dx-column data-field="bank"/>
            <dx-column data-field="sisa_piutang" data-type="number" format="#,##0"/>
            <dx-column data-field="status"/>
            <dx-column data-field="user"/>
            <dx-column data-field="created_at" data-type="date" format="dd-MM-yyyy" caption="Tgl.Post"/>
            <dx-column data-field="created_at" data-type="date" format="hh:mm" caption="Waktu"/>
            <dx-column data-field="tgl_tempo" data-type="date" format="dd-MM-yyyy"/>
            <dx-column data-field="sub_total" data-type="number" format="#,##0"/>
            <dx-column data-field="diskon" data-type="number" format="#,##0"/>
            <dx-column data-field="total" data-type="number" format="#,##0"/>
            <dx-column data-field="ppn" data-type="number" format="#,##0"/>
            <dx-column data-field="total_setelah_ppn" data-type="number" format="#,##0"/>
            <DxColumn cell-template="action" data-field="id" caption="Action"/>
            <template #action="{data:rowInfo}">
                <div class="flex items-center justify-around">
                    <a href="" v-if="rowInfo.data.status!='Finish' || rowInfo.data.departement_id==5"
                       @click.prevent="$router.push({name:'penjualan.edit',params: {id:rowInfo.data.id}})"><span
                            class="dx-icon-edit"></span></a>
                    <a href="" @click.prevent="destroy(rowInfo.data.id)"><span class="dx-icon-remove"></span></a>
                </div>
            </template>
            <dx-summary>
                <dx-total-item column="sub_total" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                <dx-total-item column="diskon" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                <dx-total-item column="total" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                <dx-total-item column="ppn" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                <dx-total-item column="total_setelah_ppn" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                <dx-total-item column="piutang" summary-type="sum" display-format="{0}" value-format="#,##0"/>
            </dx-summary>
            <dx-paging :enabled="false"/>
            <dx-filter-row :visible="true"/>
        </dx-data-grid>
    </div>
    <penjualan-informasi-rincian ref="dialogRincian"/>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging, DxSummary, DxTotalItem} from 'devextreme-vue/data-grid';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";
    import Swal from "sweetalert2";
    import PenjualanInformasiRincian from './PenjualanInformasiRincian.vue';

    const bsoft = useBoedysoft();
    const dialogRincian = ref();
    const dataRef = reactive({
        dataSource: null
    });
    const form = reactive({
        type_transaksi: 'Finish',
        tgl_awal: bsoft.DateFormat(Date.now()),
        tgl_akhir: bsoft.LastDate(Date.now()),
    })

    function refresh() {
        document.title="DAFTAR PENJUALAN";
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get('/penjualan', {params: form});
                if (data.code === 200) return data.data;
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
                useBoedysoft().axios.delete('/penjualan/' + id).then(res => {
                    if (res.data.code === 201) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Informasi..!',
                            text: res.data.message
                        });
                    } else {
                        refresh();
                    }
                });
            }
        })
    }

    onMounted(refresh);
</script>

<style scoped>

</style>
