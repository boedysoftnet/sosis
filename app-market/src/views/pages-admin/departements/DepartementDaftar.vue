<template>
    <div>
        <div class="flex items-center justify-between">
            <h2 class="title">DAFTAR DEPARTEMENT & AKSES SYSTEM</h2>
            <a href="" @click.prevent="dialogDepartement.showDialog()">
                <fa-icon icon="plus"></fa-icon>
                Buat Departement</a>
        </div>
        <div class="mt-5">
            <dx-data-grid
                    :allow-column-reordering="true"
                    :allow-column-resizing="true"
                    :column-auto-width="true"
                    :show-borders="true"
                    :focused-row-enabled="true"
                    :hover-state-enabled="true"
                    :data-source="dataRef.dataSource">
                <dx-column data-field="id" width="50"/>
                <dx-column data-field="nama"/>
                <dx-column data-field="created_at" data-type="date"/>
                <dx-column cell-template="cellAction" width="100"/>
                <template #cellAction="{data:row}">
                    <div class="flex items-center justify-around">
                        <a href="" @click.prevent="dialogDepartement.showDialog(row.data.id)"><span
                                class="fa fa-edit"></span></a>
                        <a href="" @click.prevent="destroy(row.data.id)"><span class="fa fa-remove"></span></a>
                    </div>
                </template>
                <dx-filter-row :visible="true"/>
            </dx-data-grid>
        </div>
    </div>
    <departement-register @onRefresh="refresh" ref="dialogDepartement"/>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging} from 'devextreme-vue/data-grid';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";
    import DepartementRegister from './DepartementRegister.vue';
    import * as Swal from "sweetalert2";

    const bsoft = useBoedysoft();
    const dialogDepartement = ref();
    const dataRef = reactive({
        dataSource: null,
        data: null
    });

    function refresh() {
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get('/departement');
                if (data.code === 200) {
                    dataRef.data = data.data;
                    return data.data;
                }
            }
        })
    }

    function destroy(id) {
        Swal.fire({
            title: 'Yakin Menghapus?',
            text: "Anda akan menghapus data..!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Saya yakin!'
        }).then((result) => {
            if (result.isConfirmed) {
                bsoft.axios.delete('/departement/' + id).then(refresh);
            }
        })
    }

    onMounted(refresh);
</script>

<style scoped>

</style>
