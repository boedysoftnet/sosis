<template>
    <transition>
        <div v-if="dialog"
             class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50 h-max-[450px]">
                <div class="py-1 px-5 font-semibold border-b flex justify-between items-center py-3">
                    <span>RINCIAN TRANSAKSI PRODUKSI</span>
                    <div>
                        <a href="" @click.prevent="dismisDialog"><i class="fa fa-remove"></i></a>
                    </div>
                </div>
                <div class="px-5 py-2 grid grid-cols-2 ">
                    <div>
                        <div class="grid grid-cols-2">
                            <div>Id Produksi</div>
                            <div class="">: {{produksi.id}}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div>Unit Perusahaan</div>
                            <div class="">: {{produksi.perusahaan.nama}}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div>User</div>
                            <div class="">: {{produksi.user.name}}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div>Tgl. Produksi</div>
                            <div class="">: {{produksi.tgl_produksi}}</div>
                        </div>
                    </div>
                    <div class="border-l pl-3">
                        <div class="grid grid-cols-2">
                            <div>Total Biaya Produksi</div>
                            <div class="text-right font-semibold"> {{bsoft.NumberFormat(produksi.total)}}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div>PPN</div>
                            <div class="text-right font-semibold"> {{bsoft.NumberFormat(produksi.ppn)}}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div>Biaya Produksi Setelah PPN</div>
                            <div class="text-right font-semibold"> {{bsoft.NumberFormat(produksi.total_setelah_ppn)}}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div>Estimasi Diharapkan</div>
                            <div class="text-right font-semibold"> {{produksi.estimasi}}{{produksi.barang.satuan}}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div>Selisih</div>
                            <div class="text-right font-semibold"> {{produksi.hasil_produksi-produksi.estimasi}}{{produksi.barang.satuan}}</div>
                        </div>
                        <div class="grid grid-cols-2">
                            <div>Hasil produksi</div>
                            <div class="text-right font-semibold"> {{produksi.hasil_produksi}}{{produksi.barang.satuan}}</div>
                        </div>
                        <div class="grid grid-cols-2 text-orange-500 font-semibold uppercase">
                            <div>Rekomendasi HPP</div>
                            <div class="text-right font-semibold"> Rp. {{bsoft.NumberFormat((produksi.total_setelah_ppn/produksi.hasil_produksi).toFixed(0))}}/{{produksi.barang.satuan}}</div>
                        </div>
                    </div>
                </div>
                <div class="py-2 px-3">
                    <div class="uppercase font-semibold">Informasi Bahan Baku Produksi :
                        {{produksi.barang.nama_barang}}
                    </div>
                    <div>
                        <dx-data-grid
                                :allow-column-reordering="true"
                                :allow-column-resizing="true"
                                :column-auto-width="true"
                                :show-borders="true"
                                :focused-row-enabled="true"
                                :hover-state-enabled="true"
                                :data-source="dataSource">
                            <dx-column data-field="id"/>
                            <dx-column data-field="barang.nama_barang"/>
                            <dx-column data-field="jumlah" format="#,##0"/>
                            <dx-column data-field="satuan"/>
                            <dx-column data-field="harga" format="#,##0"/>
                            <dx-column data-field="total" format="#,##0"/>
                            <dx-summary>
                                <dx-total-item column="total" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                            </dx-summary>
                        </dx-data-grid>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script setup>
    import {ref, reactive, onMounted, onActivated, computed, watch} from 'vue';
    import {DxDataGrid,DxColumn,DxSummary,DxTotalItem} from 'devextreme-vue/data-grid';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';
    import CustomStore from "devextreme/data/custom_store";

    const dialog = new ref(false);
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const produksi = ref(null);
    const dataSource = ref(null);

    function showDialog(id) {
        dialog.value = true;
        errors.value = {};
        bsoft.axios.get(`/produksi/${id}`).then(res => {
            if (res.data.code === 200) {
                produksi.value = res.data.data;
                dataSource.value = new CustomStore({
                    key: 'id',
                    load: async ()=> {
                        return await produksi.value.produksi_detail;
                    }
                });
            }
        })
    }

    function dismisDialog() {
        emit('onRefresh');
        dialog.value = false;
    }

    defineExpose({
        showDialog,
    });
</script>
<style scoped>

</style>
