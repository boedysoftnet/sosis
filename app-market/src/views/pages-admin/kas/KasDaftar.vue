<template>
    <div class="flex items-center justify-between">
        <h2 class="title font-semibold">DAFTAR KAS</h2>
        <a href="" @click.prevent="dialogKas.showDialog"><span class="fa fa-plus"></span> Register Kas</a>
    </div>
    <div class="grid-600 mt-5">
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
            <dx-column data-field="id"/>
            <dx-column data-field="akun_perkiraan.nama_akun" caption="Kategori"/>
            <dx-column data-field="catatan"/>
            <dx-column data-field="bank.nama_bank" caption="Type Pembayaran"/>
            <dx-column data-field="created_at" data-type="date" format="dd-MM-yyyy" caption="Tgl.Post"/>
            <dx-column data-field="created_at" data-type="date" format="hh:mm" caption="Waktu"/>
            <dx-column data-field="nominal" data-type="number" format="#,##0"/>
            <dx-column data-field="user.name" caption="Operator"/>
<!--            <DxColumn cell-template="action" data-field="id" caption="Action" width="50"/>-->
<!--            <template #action="{data:rowInfo}">-->
<!--                <div class="flex items-center justify-around">-->
<!--                    <a href="" @click.prevent="destroy(rowInfo.data.id)"><span class="dx-icon-remove"></span></a>-->
<!--                </div>-->
<!--            </template>-->
            <dx-summary>
                <dx-total-item column="nominal" summary-type="sum" display-format="{0}" value-format="#,##0"/>
            </dx-summary>
            <dx-filter-row :visible="true"/>
            <dx-paging enabled="false"/>
        </dx-data-grid>
    </div>
    <kas-register ref="dialogKas" @onRefresh="refresh"/>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging, DxSummary, DxTotalItem} from 'devextreme-vue/data-grid';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";
    import Swal from "sweetalert2";
    import KasRegister from './KasRegister.vue';

    const bsoft = useBoedysoft();
    const dialogKas = ref();
    const dataRef = reactive({
        dataSource: null
    });
    const form = reactive({
        type_transaksi: 'Preorder',
        tgl_awal: bsoft.FirstDate(Date.now()),
        tgl_akhir: bsoft.LastDate(Date.now()),
    })

    function refresh() {
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get('/kas', {params: form});
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
                useBoedysoft().axios.delete('/kas/' + id).then(res => {
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
