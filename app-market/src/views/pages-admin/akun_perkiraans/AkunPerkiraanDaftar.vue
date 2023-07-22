<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
            <div class="title">DAFTAR AKUN PERKIRAAN</div>
<!--            <a href="" class="dx-icon-plus" @click.prevent="register.showDialog()">Tambah</a>-->
        </div>
        <div class="mt-5 grid-700 shadow border border-basic-300 p-5 rounded bg-basic-300 bg-opacity-20">
            <dx-data-grid
                    id="gridContainer"
                    :data-source="dataRef.data"
                    :allow-column-resizing="true"
                    :column-auto-width="true"
                    :show-borders="true"
                    :focused-row-enabled="true"
                    :hover-state-enabled="true">
                <dx-column data-field="golongan" :group-index="0"/>
                <dx-column data-field="kelompok" :group-index="1"/>
                <dx-column data-field="nama_akun"/>
                <dx-column data-field="keterangan"/>
                <dx-column data-field="created_at" data-type="date"/>
                <dx-column :visible="false" cell-template="action" data-field="id" caption="Action"/>
                <template #action="{data}">
                    <div class="flex items-center justify-around">
                        <a href=""  @click.prevent="edit(data.value)"><span class="dx-icon-edit"></span></a>
                        <a href="" v-if="data.value!=1" @click.prevent="destroy(data.value)"><span class="dx-icon-remove"></span></a>
                    </div>
                </template>
                <dx-filter-row :visible="true"></dx-filter-row>
                <dx-scrolling column-rendering-mode="visual" />
                <dx-paging  :enabled="false"/>
            </dx-data-grid>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import {DxPaging,DxDataGrid, DxColumn, DxFilterRow,DxScrolling} from "devextreme-vue/data-grid";
    import CustomStore from "devextreme/data/custom_store";
    import Swal from 'sweetalert2';

    const register = ref();
    const table = ref();
    const dataRef=reactive({
        data:null
    });


    function edit(id) {
        register.value.showDialog(id);
    }

    async function refresh() {
        dataRef.data = new CustomStore({
            "key": 'id',
            "load": async () => {
                const {data} = await useBoedysoft().axios.get('/akun-perkiraan');
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

    onMounted(refresh);

</script>

<style scoped>
    #gridContainer {
        height: 740px!important;
    }
</style>
