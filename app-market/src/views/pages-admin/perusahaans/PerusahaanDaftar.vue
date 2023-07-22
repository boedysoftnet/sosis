<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
            <div class="title">Daftar Perusahaan</div>
            <a href="" class="dx-icon-plus" @click.prevent="register.showDialog()">Tambah</a>
        </div>
        <div class="mt-5">
            <dx-data-grid
                    :show-borders="true"
                    :allow-column-reordering="true"
                    :allow-column-resizing="true"
                    :column-auto-width="true"
                    :focused-row-enabled="true"
                    :data-source="dataSource">
                <dx-column data-field="id"/>
                <dx-column data-field="icon" cell-template="cellIcon" width="50"/>
                <template #cellIcon="{data}">
                    <img :src="data.value" alt="" class="w-5 mx-auto">
                </template>
                <dx-column data-field="nama"/>
                <dx-column data-field="alamat"/>
                <dx-column data-field="telpon"/>
                <dx-column data-field="email"/>
                <dx-column data-field="created_at" data-type="date"/>
                <dx-column data-field="nama_owner"/>
                <dx-column cell-template="cellAction" caption="#"/>
                <template #cellAction="{data:row}">
                    <div class="flex justify-around">
                        <a href="" @click.prevent="edit(row.data.id)"><span class="fa fa-edit"></span></a>
                        <a href="" @click.prevent="destory(row.data.id)"><span class="fa fa-remove"></span></a>
                    </div>
                </template>
            </dx-data-grid>
            <!--            <DataTables ref="table" url="/perusahaan" :fields="fields" @destory="destory" @edit="edit"></DataTables>-->
        </div>
        <PerusahaanRegister ref="register" @onRefresh="refresh"></PerusahaanRegister>
    </div>
</template>

<script setup>
    import {DxDataGrid, DxSummary, DxColumn, DxFilterRow} from 'devextreme-vue/data-grid';
    import DataTables from "../../../components/pages-admin/DataTables.vue";
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import PerusahaanRegister from './PerusahaanRegister.vue'
    import CustomStore from "devextreme/data/custom_store";
    import * as Swal from "sweetalert2";

    const register = ref();
    const table = ref();
    const dataSource = ref();

    const fields = new reactive([
        {name: 'nama'},
        {name: 'alamat'},
        {name: 'email'},
        {name: 'telpon'},
        {name: 'deskripsi'},
        {name: 'created_at', type: 'date'},
    ]);

    function edit(id) {
        register.value.showDialog(id);
    }

    function destory(id) {
        Swal.fire({
            icon: "question",
            title: "Anda yakin menghapus..?",
            text: "Data perusahaan akan dihapus..!",
            showCancelButton:true
        }).then(res => {
            if (res.isConfirmed) {
                useBoedysoft().axios.delete('/perusahaan/' + id).then(refresh);
            }
        });
    }

    function refresh() {
        dataSource.value = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await useBoedysoft().axios.get('/perusahaan');
                if (data.code === 200) return data.data;
            }
        })
    }

    onMounted(() => {
        refresh();
    })


</script>

<style scoped>

</style>
