<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
            <div class="title uppercase">Daftar Pembelian</div>
            <a href="" class="dx-icon-plus" @click.prevent="$router.push({name:'pembelian.register'})">Buat
                Pembelian</a>
        </div>
        <div class="mt-5 grid-600">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 shadow rounded border border-basic-300 bg-basic-500 bg-opacity-10 mb-2 py-1 px-2">
                <div class="mb-2">
                    <label for="">Type Transaksi</label>
                    <select class="w-full py-1 px-2 border outline-none border-basic-300" v-model="form.type_transaksi"
                            @change="refresh()">
                        <option value="Preorder">Preorder</option>
                        <option value="Khusus Terhutang">Khusus Terhutang</option>
                        <option value="Finish">Selesai</option>
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
            </div>
            <DxDataGrid id="grid"
                        :data-source="dataSource"
                        :allow-column-reordering="true"
                        :allow-column-resizing="true"
                        :column-auto-width="true"
                        :show-borders="true"
                        :focused-row-enabled="true"
                        :hover-state-enabled="true">
                <DxColumn data-field="id"/>
                <DxColumn data-field="created_at" caption="Tgl.Post" data-type="date" cell-template="cellTglPost"/>
                <template #cellTglPost="{data:rowInfo}">
                    <div class="flex items-center justify-around">
                        <a href="" @click.prevent="refPembelianDetail.showDialog(rowInfo.data.id)">{{bsoft.DateFormat(rowInfo.data.created_at)}} <span class="dx-icon-arrowright"></span></a>
                    </div>
                </template>
                <DxColumn data-field="supplier.nama"/>
                <DxColumn data-field="bank.nama_bank" caption="Type Bayar"/>
                <DxColumn data-field="no_faktur"/>
                <DxColumn data-field="sisa_hutang" data-type="number" format="#,##0"/>
                <DxColumn data-field="tgl_tempo" data-type="date"/>
                <DxColumn data-field="status"/>
                <DxColumn data-field="catatan"/>
                <DxColumn data-field="sub_total" format="#,###"/>
                <DxColumn data-field="diskon" format="#,###"/>
                <DxColumn data-field="total" format="#,###"/>
                <DxColumn data-field="ppn" format="#,###"/>
                <DxColumn data-field="total_setelah_ppn" format="#,###"/>
                <DxColumn data-field="uang_tunai" format="#,###"/>
                <DxColumn data-field="dana_transfer" format="#,###"/>
                <DxColumn cell-template="action" data-field="id" caption="Action"/>
                <template #action="{data:rowInfo}">
                    <div class="flex items-center justify-around">
                        <a href="" v-if="rowInfo.data.status!='Finish'" @click.prevent="edit(rowInfo.data.id)"><span
                                class="dx-icon-edit"></span></a>
                        <a href="" @click.prevent="destroy(rowInfo.data.id)"><span class="dx-icon-remove"></span></a>
                    </div>
                </template>
                <DxFilterRow :visible="true"></DxFilterRow>
                <dx-summary>
                    <dx-total-item column="sub_total" summary-type="sum" alignment="right" value-format="#,###"
                                   display-format="{0}"/>
                    <dx-total-item column="diskon" summary-type="sum" alignment="right" value-format="#,###"
                                   display-format="{0}"/>
                    <dx-total-item column="total" summary-type="sum" alignment="right" value-format="#,###"
                                   display-format="{0}"/>
                    <dx-total-item column="ppn" summary-type="sum" alignment="right" value-format="#,###"
                                   display-format="{0}"/>
                    <dx-total-item column="total_setelah_ppn" summary-type="sum" alignment="right" value-format="#,###"
                                   display-format="{0}"/>
                    <dx-total-item column="sisa_hutang" summary-type="sum" alignment="right" value-format="#,###"
                                   display-format="{0}"/>
                    <dx-total-item alignment="left" display-format="GRAND TOTAL" show-in-column="supplier.nama"/>
                </dx-summary>
            </DxDataGrid>
            <pembelian-informasi-rincian ref="refPembelianDetail" @onRefresh="refresh"></pembelian-informasi-rincian>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import {DxDataGrid, DxColumn, DxFilterRow, DxSummary, DxTotalItem} from "devextreme-vue/data-grid";
    import CustomStore from "devextreme/data/custom_store";
    import Swal from 'sweetalert2';
    import {useRoute, useRouter} from "vue-router";
    import pembelianInformasiRincian from './PembelianInformasiRincian.vue';

    const register = ref();
    const table = ref();
    const dataSource = ref(null);
    const refPembelianDetail= ref();
    const router = useRouter();
    const route = useRoute();
    const bsoft = useBoedysoft();
    const form = reactive({
        type_transaksi: 'Finish',
        tgl_awal: bsoft.FirstDate(Date.now()),
        tgl_akhir: bsoft.LastDate(Date.now()),
    })

    function edit(id) {
        router.push({name: 'pembelian.edit', params: {id}});
    }

    async function refresh() {
        dataSource.value = new CustomStore({
            "key": 'id',
            "load": async () => {
                const {data} = await useBoedysoft().axios.get('/pembelian', {params: form});
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
                useBoedysoft().axios.delete('/pembelian/' + id).then(res => {
                    if (res.data.code === 201) {
                        Swal.fire({
                            icon:'warning',
                            title:'Informasi..!',
                            text:res.data.message
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
<style>
    #grid {
        height: 100px;
    }
</style>

