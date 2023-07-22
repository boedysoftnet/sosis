<template>
    <transition>
        <!--todo perhatikan saat pembelian retur artinya jika nilai minus system pembukuan accounting belum benar-->
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-4/5 shadow bg-basic-50 ">
                <div class="py-3 text-xl px-5 font-semibold border-b flex items-center justify-between">
                    <h2>ARUS PEMBUKUAN</h2>
                </div>
                <div class="mb-2 px-3">
                    <label for="">Sampai Periode</label>
                    <input type="date" v-model="periode" @change="refresh">
                </div>
                <div class="flex flex-col space-y-2">
                    <dx-data-grid
                            id="data-grid"
                            class="flex-1 h-screen"
                            :allow-column-resizing="true"
                            :column-auto-width="true"
                            :show-borders="true"
                            :focused-row-enabled="true"
                            :hover-state-enabled="true"
                            :data-source="dataSource">
                        <dx-column data-field="kategori" :visible="false"/>
                        <dx-column data-field="catatan" cell-template="cellCustom"/>
                        <template #cellCustom="{data:row}">
                            <div>
                                <div class="font-semibold">
                                    {{row.data.catatan}}
                                </div>
                                <div><span class="fa fa-clock-o mr-1"></span>{{row.data.created_at}} By :
                                    {{row.data.user}}
                                </div>
                            </div>
                        </template>
                        <dx-column data-field="debet" data-type="number" format="#,##0" width="150"/>
                        <dx-column data-field="kredit" data-type="number" format="#,##0" width="150"/>
                        <dx-column data-field="total" data-type="number" format="#,##0" width="150"/>
                        <dx-paging :enabled="false"/>
                        <dx-summary>
                            <dx-total-item column="debet" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                            <dx-total-item column="kredit" summary-type="sum" display-format="{0}"
                                           value-format="#,##0"/>
                        </dx-summary>
                        <dx-filter-row :visible="true"/>
                    </dx-data-grid>
                    <div class="flex justify-end space-x-2 py-3 px-3">
                        <button type="button" class="bg-basic-300" @click.prevent="dismisDialog"><i
                                class="fa fa-arrow-left"></i> Tutup
                        </button>
                        <button class="bg-basic-300" @click.prevent="dialogPrint.showPrint(myId)"><i class="fa fa-print"></i> Print</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
    <arus-pembukuan-print ref="dialogPrint" :periode="periode" class="hidden"></arus-pembukuan-print>
</template>
<script setup>
    import {ref, reactive, computed, onMounted, onActivated, watch} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';
    import {DxDataGrid, DxColumn, DxTotalItem, DxSummary, DxPaging, DxFilterRow} from 'devextreme-vue/data-grid';
    import {useRouter} from "vue-router";
    import CustomStore from "devextreme/data/custom_store";
    import ArusPembukuanPrint from './ArusPembukuanPrint.vue';

    const dialog = new ref(false);
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const router = useRouter();
    const dataSource = ref();
    const periode = ref(bsoft.LastDate(Date.now()))
    const myId = ref('');
    const dialogPrint = ref();

    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        myId.value = id;
        refresh();
    }

    function refresh() {
        dataSource.value = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get('/jurnal/akun/' + myId.value, {params: {tgl_akhir: periode.value}});
                return data.data;
            }
        })
    }

    function dismisDialog() {
        dialog.value = false;
        emit('onRefresh');
    }

    defineExpose({
        showDialog,
    });
</script>
<style type="text/css">
    #data-grid .dx-gridbase-container {
        height: 40em;
    }
</style>
