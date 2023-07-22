<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
            <div class="title">Daftar Supplier</div>
            <a href="" class="dx-icon-plus" @click.prevent="register.showDialog()">Tambah</a>
        </div>
        <div class="mt-5">
            <DataTables ref="table" url="/supplier" :fields="fields" @destory="destory" @edit="edit"></DataTables>
        </div>
        <SupplierRegister ref="register" @onRefresh="table.refresh()"></SupplierRegister>
    </div>
</template>

<script setup>
    import DataTables from "../../../components/pages-admin/DataTables.vue";
    import {ref,reactive,onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import SupplierRegister from './SupplierRegister.vue'

    const register=ref();
    const table=ref();

    const fields=new reactive([
        {name:'nama'},
        {name:'alamat'},
        {name:'email'},
        {name:'telpon'},
        {name:'ppn',alias:'PPN'},
        {name:'keterangan'},
        {name:'created_at',type:'date'},
    ]);

    function edit(id) {
        register.value.showDialog(id);
    }
    function destory(id){
        useBoedysoft().axios.delete('/supplier/' + id);
        table.value.refresh();
    }
    onMounted(()=>{
    })


</script>

<style scoped>

</style>
