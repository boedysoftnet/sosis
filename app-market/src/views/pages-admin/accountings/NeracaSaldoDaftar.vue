<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
            <div class="title flex justify-between items-center w-full">
                <div>DAFTAR NERACA SALDO</div>
<!--                <a href="" @click.prevent="resetDatabase">Reset Data</a>-->
            </div>
<!--            <a href="" class="dx-icon-plus" @click.prevent="register.showDialog()">Tambah</a>-->
        </div>
        <div class="mt-5 grid-700 shadow border border-basic-300 p-5 rounded bg-basic-300 bg-opacity-20">
            <div class="my-2">
                <label for="">Periode </label>
                <input type="date" v-model="periode" @change="refresh" class="py-1 px-2 border border-basic-300 outline-none w-full">
            </div>
            <dx-data-grid
                    id="gridContainer"
                    :data-source="dataRef.data"
                    :allow-column-resizing="true"
                    :column-auto-width="true"
                    :show-borders="true"
                    :focused-row-enabled="true"
                    :hover-state-enabled="true">
                <dx-column data-field="kode_akun" width="90"/>
                <dx-column data-field="golongan" :group-index="0"/>
                <dx-column data-field="kelompok" :group-index="1"/>
                <dx-column data-field="nama_akun"/>
                <dx-column data-field="debet" format="#,##0" data-type="number"/>
                <dx-column data-field="kredit" format="#,##0" data-type="number"/>
                <dx-column :visible="true" cell-template="action" data-field="id" caption="Action" width="50"/>
                <template #action="{data:row}">
                    <div class="flex items-center justify-around">
                        <a href=""  @click.prevent="dialogArus.showDialog(row.data.id)"><span class="fa fa-arrow-right"></span></a>
                    </div>
                </template>
                <dx-filter-row :visible="true"></dx-filter-row>
                <dx-scrolling column-rendering-mode="visual" />
                <dx-paging  :enabled="false"/>
                <dx-summary>
                    <dx-total-item column="debet" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                    <dx-total-item column="kredit" summary-type="sum" display-format="{0}" value-format="#,##0"/>
                </dx-summary>
            </dx-data-grid>
        </div>
        <arus-pembukuan ref="dialogArus"/>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import {DxPaging,DxDataGrid, DxColumn, DxFilterRow,DxScrolling,DxSummary,DxTotalItem} from "devextreme-vue/data-grid";
    import CustomStore from "devextreme/data/custom_store";
    import Swal from 'sweetalert2';
    import ArusPembukuan from './ArusPembukuan.vue';

    const register = ref();
    const table = ref();
    const dataRef=reactive({
        data:null
    });
    const periode=ref();
    const bsoft=useBoedysoft();
    const dialogArus=ref();


    function edit(id) {
        register.value.showDialog(id);
    }

    async function refresh() {
        dataRef.data = new CustomStore({
            "key": 'id',
            "load": async () => {
                const {data} = await useBoedysoft().axios.get('/akun-perkiraan',{params:{periode:periode.value}});
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
                useBoedysoft().axios.delete('/akun-perkiraan/' + id);
                refresh();
            }
        })
    }

    function resetDatabase(){
        bsoft.axios.post('/reset-database').then(res=>{
            if(res.data.code===200) Swal.fire({
                icon:"success",
                title:'Reset Data Berhasil..!',
                text:res.data.message
            }).then(()=>refresh());
        })
    }
    onMounted(()=>{
        periode.value=bsoft.LastDate(Date.now());
        refresh();
    });


</script>

<style scoped>
    #gridContainer {
        height: 740px!important;
    }
</style>
