<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
            <div class="title">Daftar Bank</div>
            <a href="" class="dx-icon-plus" @click.prevent="register.showDialog()">Tambah</a>
        </div>
        <div class="mt-5">
            <DxDataGrid
                    :data-source="dataSource"
                    :allow-column-reordering="true"
                    :allow-column-resizing="true"
                    :column-auto-width="true"
                    :show-borders="true"
                    :focused-row-enabled="true"
                    :hover-state-enabled="true">
                <DxColumn data-field="nama_bank"/>
                <DxColumn data-field="alamat"/>
                <DxColumn data-field="telpon"/>
                <DxColumn data-field="email"/>
                <DxColumn data-field="atas_nama"/>
                <DxColumn data-field="nomor_rekening"/>
                <DxColumn data-field="catatan"/>
                <DxColumn data-field="created_at" data-type="date"/>
                <DxColumn cell-template="action" data-field="id" caption="Action"/>
                <template #action="{data}">
                    <div class="flex items-center justify-around">
                        <a href=""  @click.prevent="edit(data.value)"><span class="dx-icon-edit"></span></a>
                        <a href="" v-if="data.value!=1" @click.prevent="destroy(data.value)"><span class="dx-icon-remove"></span></a>
                    </div>
                </template>
                <DxFilterRow :visible="true"></DxFilterRow>
            </DxDataGrid>
        </div>
        <BankRegister ref="register" @onRefresh="refresh()"></BankRegister>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import BankRegister from './BankRegister.vue';
    import {DxDataGrid, DxColumn, DxFilterRow} from "devextreme-vue/data-grid";
    import CustomStore from "devextreme/data/custom_store";
    import Swal from 'sweetalert2';

    const register = ref();
    const table = ref();
    const dataSource = ref(null);
    

    function edit(id) {
        register.value.showDialog(id);
    }

    async function refresh() {
        dataSource.value = new CustomStore({
            "key": 'id',
            "load": async () => {
                const {data} = await useBoedysoft().axios.get('/bank');
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
                useBoedysoft().axios.delete('/bank/' + id);
                refresh();
            }
        })
    }

    onMounted(refresh);

</script>

<style scoped>

</style>
