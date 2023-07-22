<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50">
                <div class="py-3 px-5 font-semibold border-b">Register Barang</div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan()">
                        <div class="mt-2">
                            <label for="">Nama Perusahaan</label>
                            <input type="text" v-model="form.nama"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                            <ValidateError :errors="errors" name="nama"></ValidateError>
                        </div>
                        <div class="mt-2">
                            <label for="">Alamat</label>
                            <input type="text" v-model="form.alamat"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                            <ValidateError :errors="errors" name="alamat"></ValidateError>
                        </div>
                        <div>
                            <label for="">Icon</label>
                            <div class="flex items-center space-x-2">
                                <img v-if="form.url" :src="form.url" alt="" class="w-10">
                                <div class="flex-1">
                                    <input type="file" @change="selectImage" accept="image/*"/>
                                    <validate-error name="email" :errors="errors"/>
                                </div>
                            </div>
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
                            <label for="">Keterangan</label>
                            <input type="text" v-model="form.keterangan"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                            <ValidateError :errors="errors" name="keterangan"></ValidateError>
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

    const dialog = new ref(false);
    const meta = {
        id: null,
        alamat: null,
        created_at: null,
        updated_at: null,
        deleted_at: null,
        keterangan: null,
        nama: null,
        owner_id: null,
        photo: null,
        telpon: null,
        url: null,
    }
    let form = reactive({...meta});
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});

    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        Object.assign(form,meta);
        if (id) {
            useBoedysoft().axios.get('/perusahaan/' + id).then(({data}) => {
                if (data.code === 200) {
                    Object.assign(form, data.data);
                }
            })
        }
    }

    function dismisDialog() {
        dialog.value = false;
        emit('onRefresh');
    }

    function selectImage(event) {
        if (event.target.files.length != 0) {
            form.icon = event.target.files[0];
            let fileReader = new FileReader();
            fileReader.readAsDataURL(form.icon);
            fileReader.addEventListener('load', () => {
                form.url = fileReader.result
            });
        }
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
        let frm=new FormData();
        frm.append('nama',form.nama);
        frm.append('alamat',form.alamat);
        frm.append('telpon',form.telpon);
        frm.append('email',form.email);
        frm.append('keterangan',form.keterangan);
        frm.append('icon',form.icon);
        if (form.id != undefined) {
            let {data} = await bsoft.axios.post('/perusahaan/' + form.id, frm);
            if (data.code === 200) {
                sukses(data);
            } else {
                errors.value = data.data;
            }
        } else {
            let {data} = await bsoft.axios.post('/perusahaan', frm);
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
