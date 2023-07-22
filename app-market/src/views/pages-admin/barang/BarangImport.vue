<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50 h-max-[450px]">
                <div class="py-3 px-5 font-semibold border-b">Import Barang</div>
                <div class="py-3 px-5">
                    <div>
                        <input type="file" @change="onFileChange"/>
                    </div>
                    <div class="space-x-2 flex justify-end">
                        <button class="mt-2 bg-basic-300" type="button" @click.prevent="dismisDialog"><span class="fa fa-arrow-left"></span> Tutup</button>
                        <button class="mt-2 bg-basic-300" type="button" @click.prevent="proses"><span class="fa fa-save"></span> Proses</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import readXlsxFile from 'read-excel-file';
    import Swal from 'sweetalert2';
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging} from "devextreme-vue/data-grid";
    import CustomStore from "devextreme/data/custom_store";

    const tabs = ref(0);
    const dialog = new ref(false);
    const emit = defineEmits(['onRefresh']);
    const dumData = ref();
    const dataSource = ref();
    const bsoft=useBoedysoft();
    const frm=ref(new FormData())

    function showDialog(id = null) {
        dialog.value = true;
    }

    function proses() {
        bsoft.axios.post('/barang/import',frm.value).then(({data})=>{
            if(data.code===200){
                Swal.fire({
                    icon:"success",
                    title:data.message
                }).then(dismisDialog);
            }else{
                Swal.fire({
                    icon:"error",
                    title:"Maaf mohon check format data excel anda..!"
                });
            }
        });
    }

    function onFileChange(event) {
        let xlsxfile = event.target.files ? event.target.files[0] : null;
        frm.value.append('excel',xlsxfile);
        dumData.value = [];
        /*todo lanjutkan untuk membuat import data barang segera*/
        readXlsxFile(xlsxfile, {sheet: 'data-barang'}).then((rows) => {
            rows.forEach((value, keys) => {
                if (keys > 0) {
                    let col = [];
                    rows[0].forEach((column, key) => {
                        col[column] = value[key];
                    });
                    dumData.value.push(col);
                }
            })
        });
    }

    function refresh() {
        dataSource.value = new CustomStore({
            "key": 'id',
            "data": async () => {
                return dumData.value;
            }
        })
    }

    function dismisDialog() {
        dialog.value = false;
        emit('onRefresh');
    }

    defineExpose({
        showDialog,
    });
</script>
<style scoped>

</style>
