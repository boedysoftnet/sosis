<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50 h-max-[450px]">
                <div class="py-2 px-5 font-semibold border-b flex flex-col items-center justify-between">
                    <h2>KONFIRMASI</h2>
                    <p class="text-sm italic text-basic-400">Mengapa anda menguragi persediaan barang, berikan
                        keterangan singkat dan jelas..</p>
                </div>
                <div class="py-3 px-5">
                    <textarea cols="30" rows="3" placeholder="Silakan isikan keterangan anda..!" v-model="catatan"></textarea>
                </div>
                <div class="flex space-x-2 pb-4 px-4 justify-end">
                    <button type="button" class="bg-basic-200 space-x-2" @click.prevent="dismisDialog">
                        <fa-icon icon="arrow-left"></fa-icon>
                        <span>Batal</span></button>
                    <button type="submit" @click.prevent="kirim" class="bg-basic-200 space-x-2">
                        <fa-icon icon="save"></fa-icon>
                        <span>Simpan</span></button>
                </div>
            </div>
        </div>
    </transition>
</template>
<script setup>
    import {ref, reactive, computed, onMounted, onActivated, watch} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';
    import {useRouter} from "vue-router";

    const dialog = new ref(false);
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const router = useRouter();
    const catatan = ref('');


    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
    }

    function dismisDialog() {
        dialog.value = false;
        emit('onRefresh');
    }

    function kirim() {
        if (catatan.value!=''){
            emit('onSave', catatan.value);
            catatan.value='';
        }else{
            Swal.fire({
                icon:'warning',
                title:'Peringatan!',
                text:'Anda tidak dapat menyimpan ini tanpa memberikan alasan mengapa stock dikurangi..!'
            })
        }
    }

    defineExpose({
        showDialog,dismisDialog
    });
</script>
<style scoped>

</style>
