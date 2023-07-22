<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
            <div class="title">Daftar Operator</div>
            <a href="" class="dx-icon-plus" @click.prevent="register.showDialog()">Tambah</a>
        </div>
        <div class="mt-5">
            <DataTables ref="table" url="/user" :fields="fields" @destory="destory" @edit="edit"></DataTables>
        </div>
        <UserRegister ref="register" @onRefresh="table.refresh()"></UserRegister>
    </div>
</template>

<script setup>
    import DataTables from "../../../components/pages-admin/DataTables.vue";
    import UserRegister from "./UserRegister.vue";
    import {ref,reactive,onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";

    const register=ref();
    const table=ref();

    const fields=new reactive([
        {name:'name'},
        {name:'email'},
        {name:'departement.nama'},
        {name:'perusahaan.nama',alias:'Unit Perusahaan'},
        {name:'created_at',type:'date'},
    ]);

    function edit(id) {
        register.value.showDialog(id);
    }
    function destory(id){
        useBoedysoft().axios.delete('/user/' + id);
        table.value.refresh();
    }
    onMounted(()=>{
    })


</script>

<style scoped>

</style>
