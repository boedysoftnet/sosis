<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-3/4 shadow bg-basic-50 h-max-[450px]">
                <div class="py-3 px-5 font-semibold border-b">Rincian {{datas.data[0].barang.nama_barang}}</div>
                <div class="py-3 px-5">
                    <dx-data-grid :data-source="datas.dataSource"
                                  :column-auto-width="true"
                                  :focused-row-enabled="true"
                                  :show-borders="true">
                        <dx-column cell-template="cellPembelianId" caption="Pembelian ID" width="80"/>
                        <template #cellPembelianId="{data:row}">
                            <a href="" @click.prevent="showDialogRincian(row.data.pembelian_id)" class="flex justify-between items-center" title="Click untuk mendapatkan detail dari pembelian..!">
                                <h2>{{row.data.pembelian_id}}</h2>
                                <span class="dx-icon-arrowright"></span>
                            </a>
                        </template>
                        <dx-column data-field="created_at" data-type="date" format="d MMM y H:m"/>
                        <dx-column data-field="qty"/>
                        <dx-column data-field="satuan"/>
                        <dx-column data-field="harga" format="#,##0"/>
                        <dx-column data-field="sub_total" format="#,##0"/>
                        <dx-column data-field="diskon" format="#,##0"/>
                        <dx-column data-field="total" format="#,##0"/>
                        <dx-column data-field="ppn" format="#,##0"/>
                        <dx-column data-field="total_setelah_ppn" format="#,##0"/>
                        <dx-summary>
                            <dx-total-item column="qty" summary-type="sum" display-format="{0}"
                                           value-format="#,##0"/>
                            <dx-total-item column="sub_total" summary-type="sum" display-format="{0}"
                                           value-format="#,##0"/>
                            <dx-total-item column="diskon" summary-type="sum" display-format="{0}"
                                           value-format="#,##0"/>
                            <dx-total-item column="total" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                            <dx-total-item column="ppn" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                            <dx-total-item column="total_setelah_ppn" summary-type="sum" display-format="{0}"
                                           value-format="#,##0"/>
                        </dx-summary>
                    </dx-data-grid>
                </div>
                <div class="py-2 px-4 text-right">
                    <button type="button" class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50"
                            @click.prevent="dismisDialog"><span class="dx-icon-back"></span>Tutup
                    </button>
                </div>
                <pembelian-informasi-rincian ref="refPembelianInformasi"></pembelian-informasi-rincian>
            </div>
        </div>
    </transition>
</template>
<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';
    import {DxDataGrid, DxColumn, DxFilterRow, DxSummary, DxTotalItem} from 'devextreme-vue/data-grid';
    import CustomStore from "devextreme/data/custom_store";
    import {useRouter} from "vue-router";
    import PembelianInformasiRincian from '../pembelians/PembelianInformasiRincian.vue';

    const dialog = new ref(false);
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const datas = reactive({
        data: null,
        dataSource: null
    })
    const router = useRouter();
    const refPembelianInformasi=ref();

    function showDialogRincian(id) {
        refPembelianInformasi.value.showDialog(id);
    }

    function showDialog(pembelianDetail) {
        dialog.value = true;
        errors.value = {};
        datas.data = pembelianDetail;
        refresh(pembelianDetail);
    }

    function refresh(pembelianDetail) {
        datas.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                return pembelianDetail;
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
<style scoped>

</style>
