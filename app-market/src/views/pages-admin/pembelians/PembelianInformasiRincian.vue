<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-4/5 shadow bg-basic-50 h-max-[300px] grid-300">
                <div class="py-2 px-5 font-semibold border-b flex items-center justify-between">
                    <h2>RINCIAN PEMBELIAN</h2>
                </div>
                <div class="py-3 px-5 h-max overflow-auto">
                    <div class="grid grid-cols-2 mb-4">
                        <div>
                            <div class="grid grid-cols-4">
                                <label for="">Supplier</label>
                                <p class="font-semibold col-span-3">{{dataRef.pembelian.supplier.nama}}</p>
                            </div>
                            <div class="grid grid-cols-4">
                                <label for="">Telpon</label>
                                <p class="font-semibold col-span-3">{{dataRef.pembelian.supplier.telpon}}</p>
                            </div>
                            <div class="grid grid-cols-4">
                                <label for="">Status Transaksi</label>
                                <p class="font-semibold col-span-3">{{dataRef.pembelian.status}}</p>
                            </div>
                            <div class="grid grid-cols-4">
                                <label for="">Tgl.Post</label>
                                <p class="font-semibold col-span-3">{{bsoft.DateFormat(dataRef.pembelian.created_at,'D MMM Y ~ hh:ss')}}</p>
                            </div>
                        </div>
                        <div v-if="dataRef.pembelian.hutang>0">
                            <div class="grid grid-cols-4">
                                <label for="">Hutang</label>
                                <p class="font-semibold col-span-3">{{bsoft.NumberFormat(dataRef.pembelian.hutang)}}</p>
                            </div>
                            <div class="grid grid-cols-4">
                                <label for="">Terbayar</label>
                                <p class="font-semibold col-span-3">{{bsoft.NumberFormat(dataRef.pembelian.terbayar)}}</p>
                            </div>
                            <div class="grid grid-cols-4">
                                <label for="">Sisa Hutang</label>
                                <p class="font-semibold col-span-3">{{bsoft.NumberFormat(dataRef.pembelian.sisa_hutang)}}</p>
                            </div>
                            <div class="grid grid-cols-4">
                                <label for="">Tgl. Jatuh Tempo</label>
                                <p class="font-semibold col-span-3">{{bsoft.DateFormat(dataRef.pembelian.tgl_tempo)}}</p>
                            </div>
                        </div>
                    </div>
                    <dx-data-grid :data-source="dataRef.dataSource"
                                  :allow-column-resizing="true"
                                  :column-auto-width="true"
                                  :show-borders="true"
                                  :focused-row-enabled="true">
                        <dx-column data-field="id"/>
                        <dx-column data-field="barang.nama_barang" caption="Nama Barang"/>
                        <dx-column data-field="harga" format="#,##0"/>
                        <dx-column data-field="qty" format="#,##0.0"/>
                        <dx-column data-field="satuan"/>
                        <dx-column data-field="sub_total" format="#,###"/>
                        <dx-column data-field="diskon" format="#,###"/>
                        <dx-column data-field="total" format="#,###"/>
                        <dx-column data-field="ppn" format="#,###"/>
                        <dx-column data-field="total_setelah_ppn" format="#,###"/>
                        <dx-column data-field="expaid" data-type="date" format="dd-MM-y"/>
                        <dx-filter-row :visible="true"></dx-filter-row>
                        <dx-summary>
                            <dx-total-item column="sub_total" summary-type="sum" display-format="{0}" value-format="#,###"/>
                            <dx-total-item column="diskon" summary-type="sum" display-format="{0}" value-format="#,###"/>
                            <dx-total-item column="total" summary-type="sum" display-format="{0}" value-format="#,###"/>
                            <dx-total-item column="ppn" summary-type="sum" display-format="{0}" value-format="#,###"/>
                            <dx-total-item column="total_setelah_ppn" summary-type="sum" display-format="{0}" value-format="#,###"/>
                        </dx-summary>
                    </dx-data-grid>
                </div>
                <div class="text-right p-2">
                    <button type="button" class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50"
                            @click.prevent="dismisDialog"><span class="dx-icon-back "></span> Tutup
                    </button>
                    <button type="button" class="py-1 px-2 shadow ml-2 bg-blue-500 text-basic-50" v-if="dataRef.pembelian.sisa_hutang>0"
                            @click.prevent="dialogSetoran.showDialog(dataRef.pembelian.id)"><span class="dx-icon-attach "></span> Setor Hutang
                    </button>
                    <pembelian-setoran-hutang ref="dialogSetoran" @onRefresh="refresh"/>
                    <button type="button" class="py-1 px-2 shadow ml-2 bg-blue-500 text-basic-50" v-if="dataRef.pembelian.status==='Preorder'"
                            @click.prevent="router.push({name:'pembelian.edit',params:{id:dataRef.pembelian.id}})"><span class="dx-icon-attach "></span> Proses Order
                    </button>
                    <button class="py-1 px-2 shadow ml-2 bg-yellow-500 text-basic-50" type="button" @click.prevent="dialogPrint.showPrint(dataRef.pembelian.id)"><span
                            class="dx-icon-print"></span> Print Preorder
                    </button>
                    <pembelian-print-preorder class="hidden" ref="dialogPrint"/>
                </div>
            </div>
        </div>
    </transition>
</template>
<script setup>
    import {ref, reactive, onMounted, onActivated, watch} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';
    import {useRouter} from "vue-router";
    import {DxDataGrid, DxColumn, DxFilterRow, DxSummary, DxTotalItem} from "devextreme-vue/data-grid";
    import CustomStore from "devextreme/data/custom_store";
    import pembelianSetoranHutang from './PembelianSetoranHutang.vue';
    import PembelianPrintPreorder from './PembelianPrintPreorder.vue'

    const dialog = new ref(false);
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const dialogSetoranHutang =ref();
    const dataRef = reactive({
        dataSource: null,
        pembelian: {
            supplier:{
                nama:''
            }
        }
    });
    const router = useRouter();
    const dialogSetoran=ref();
    const dialogPrint=ref();

    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        refresh(id);
    }

    function refresh(id=null) {
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get(`/pembelian/${id}`);
                dataRef.pembelian=data.data;
                return data.data.pembelian_detail;
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
    .dx-gridbase-container {
        height: 240px;
    }
</style>
