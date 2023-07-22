<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50 h-max-[450px]">
                <div class="py-3 px-5 font-semibold border-b">Register Bank</div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan">
                        <div class="mt-2">
                            <div class="mt-2">
                                <label for="">Nama Bank</label>
                                <input type="text" v-model="form.nama_bank"
                                       class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                <ValidateError :errors="errors" name="nama_bank"></ValidateError>
                            </div>
                            <div class="grid grid-cols-2 gap-2 border shadow border-basic-200 px-3 pb-2 my-2">
                                <div class="mt-2">
                                    <label for="">Atas Nama</label>
                                    <input type="text" v-model="form.atas_nama"
                                           class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                    <ValidateError :errors="errors" name="atas_nama"></ValidateError>
                                </div>
                                <div class="mt-2">
                                    <label for="">Nomor Rekening</label>
                                    <input type="text" v-model="form.nomor_rekening"
                                           class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                    <ValidateError :errors="errors" name="nomor_rekening"></ValidateError>
                                </div>
                            </div>
                            <div class="mt-2">
                                <label for="">Alamat</label>
                                <input type="text" v-model="form.alamat"
                                       class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                <ValidateError :errors="errors" name="alamat"></ValidateError>
                            </div>
                            <div class="mt-2">
                                <label for="">Telpon</label>
                                <input type="text" v-model="form.telpon"
                                       class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                <ValidateError :errors="errors" name="telpon"></ValidateError>
                            </div>
                            <div class="mt-2">
                                <label for="">Email</label>
                                <input type="text" v-model="form.email"
                                       class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                <ValidateError :errors="errors" name="email"></ValidateError>
                            </div>
                            <div class="mt-2">
                                <label for="">Catatan</label>
                                <input type="text" v-model="form.catatan"
                                       class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                <ValidateError :errors="errors" name="catatan"></ValidateError>
                            </div>
                        </div>
                        <div class="py-2 text-right">
                            <button type="button" class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50"
                                    @click.prevent="dismisDialog">Batal
                            </button>
                            <button class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>
<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';

    const tabs=ref(0);
    const dialog = new ref(false);
    let form = reactive({});
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});

    /*todo jangan menyerah cari terus sampai ketemu untuk kelola array reactive yach budi*/
    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        tabs.value=0;
        Object.assign(form, {
            nama_bank:null,
            alamat:null,
            telpon:null,
            email:null,
            catatan:null,
        });
        if (id) {
            useBoedysoft().axios.get('/bank/' + id).then(({data}) => {
                Object.assign(form,data.data);
            })
        }
    }

    function dismisDialog() {
        dialog.value = false;
        emit('onRefresh');
    }

    function sukses(res) {
        dismisDialog();
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: res.message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => dismisDialog())
    }

    async function simpan() {
        if (form.id != undefined) {
            let {data} = await bsoft.axios.put('/bank/' + form.id, form);
            if (data.code === 200) {
                sukses(data);
            } else {
                errors.value = data.data;
            }
        } else {
            let {data} = await bsoft.axios.post('/bank', form);
            if (data.code === 200) {
                sukses(data);
            } else {
                errors.value = data.data;
            }
        }
    }


    defineExpose({
        showDialog,
    });
</script>
<style scoped>

</style>
