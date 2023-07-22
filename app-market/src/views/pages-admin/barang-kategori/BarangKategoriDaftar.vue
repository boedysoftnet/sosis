<template>
    <div class="boedysoft">
        <div class="flex items-center justify-between">
            <div class="title">Daftar Kategori Barang</div>
            <a href="" class="dx-icon-plus" @click.prevent="register.showDialog()">Tambah</a>
        </div>
        <div class="mt-5">
            <DataTables ref="table" url="/barang-kategori" :fields="fields" @destory="destory" @edit="edit"></DataTables>
        </div>
        <BarangKategoriRegister ref="register" @onRefresh="table.refresh()"></BarangKategoriRegister>
    </div>
</template>

<script setup>
    import DataTables from "../../../components/pages-admin/DataTables.vue";
    import {ref,reactive,onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import BarangKategoriRegister from './BarangKategoriRegister.vue'

    const register=ref();
    const table=ref();

    const fields=new reactive([
        {name:'id',width:50},
        {name:'kategori'},
        {name:'deskripsi'},
        {name:'jumlah_barang'},
        {name:'created_at',type:'date'},
    ]);

    function edit(id) {
        register.value.showDialog(id);
    }
    function destory(id){
        useBoedysoft().axios.delete('/barang-kategori/' + id);
        table.value.refresh();
    }
    onMounted(()=>{
    })


</script>

<style scoped>

</style>
