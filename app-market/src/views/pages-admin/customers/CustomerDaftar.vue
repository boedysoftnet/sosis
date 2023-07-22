<template>
    <div>
        <div class="border-b border-dashed border-basic-500 flex items-center justify-between py-2">
            <h2 class="text-xl font-semibold">DAFTAR CUSTOMER</h2>
            <button class="bg-basic-300 border-none text-white dark:bg-white dark:text-black" @click.prevent="dialogCustomer.showDialog()">
                <fa-icon icon="plus"></fa-icon>
                Buat Customer
            </button>
        </div>
        <div class="grid-600">
            <dx-data-grid
                    :show-borders="true"
                    :allow-column-reordering="true"
                    :allow-column-resizing="true"
                    :column-auto-width="true"
                    :focused-row-enabled="true"
                    :data-source="dataRef.dataSource">
                <dx-column cell-template="cellPhoto" caption="Photo" width="50"/>
                <dx-column data-field="nama"/>
                <template #cellPhoto="{data:row}">
                    <img :src="row.data.url" alt="" class="w-10 shadow rounded-md">
                </template>
                <dx-column data-field="alamat"/>
                <dx-column data-field="email"/>
                <dx-column data-field="telpon"/>
                <dx-column data-field="catatan"/>
                <dx-column data-field="created_at" data-type="date"/>
                <dx-column data-field="join"/>
                <dx-column cell-template="cellAction" width="80"/>
                <template #cellAction="{data:row}" >
                    <div class="flex justify-around">
                        <a href="" @click.prevent="dialogCustomer.showDialog(row.data.id)"><fa-icon icon="edit"></fa-icon></a>
                        <a href="" @click.prevent="destory(row.data.id)"><fa-icon icon="remove"></fa-icon></a>
                    </div>
                </template>
                <dx-filter-row :visible="true"/>
                <dx-paging :enabled="false"/>
            </dx-data-grid>
            <customer-register ref="dialogCustomer" @onRefresh="refresh"/>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging} from 'devextreme-vue/data-grid';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import CustomStore from "devextreme/data/custom_store";
    import CustomerRegister from './CustomerRegister.vue';
    import Swal from 'sweetalert2';

    const bsoft = useBoedysoft();
    const dataRef = reactive({
        dataSource: null
    });
    const dialogCustomer = ref();

    function refresh() {
        dataRef.dataSource = new CustomStore({
            key: 'id',
            load: async () => {
                let {data} = await bsoft.axios.get('/customer');
                return data.data;
            }
        })
    }

    function destory(id){
        Swal.fire({
            icon:'warning',
            titile:'Hapus data..?',
            text:'Anda yakin menghapus data ini..?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Saya yakin!'
        }).then((result) => {
            if (result.isConfirmed) {
                bsoft.axios.delete(`/customer/${id}`).then(res=>{
                    refresh();
                });
            }
        });
    }
    onMounted(refresh)

</script>

<style scoped>

</style>
