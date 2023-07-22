<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50">
                <div class="py-3 px-5 font-semibold border-b">Register User</div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan()">
                        <div class="mt-2">
                            <label for="">Nama</label>
                            <input type="text" v-model="form.name"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                            <ValidateError :errors="errors" name="name"></ValidateError>
                        </div>
                        <div>
                            <label for="">Photo</label>
                            <div class="flex items-center space-x-2">
                                <img v-if="form.url" :src="form.url" alt="" class="w-10">
                                <div class="flex-1">
                                    <input type="file" @change="selectImage" accept="image/*"/>
                                    <validate-error name="email" :errors="errors"/>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label for="">Unit Perusahaan</label>
                            <select v-model="form.perusahaan_id"
                                    class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                <option v-for="it in perusahaans" :value="it.id">{{it.nama}}</option>
                            </select>
                            <ValidateError :errors="errors" name="name"></ValidateError>
                        </div>
                        <div class="mt-2">
                            <label for="">Departement</label>
                            <select v-model="form.departement_id"
                                    class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                <option v-for="it in departements" :value="it.id">{{it.nama}}</option>
                            </select>
                            <ValidateError :errors="errors" name="name"></ValidateError>
                        </div>
                        <div class="mt-2">
                            <label for="">Email</label>
                            <input type="text" v-model="form.email"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                            <ValidateError :errors="errors" name="email"></ValidateError>
                        </div>
                        <div class="mt-2">
                            <label for="">Password</label>
                            <input type="password" v-model="form.password"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                            <ValidateError :errors="errors" name="password"></ValidateError>
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
    let form = reactive({});
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const perusahaans = ref([]);
    const departements = ref([]);

    onMounted([
        getPerusahaan, getDepartemen
    ]);

    async function getPerusahaan() {
        let {data} = await bsoft.axios.get('/perusahaan');
        if (data.code === 200) perusahaans.value = data.data;
    }

    function selectImage(event) {
        if (event.target.files.length != 0) {
            form.photo = event.target.files[0];
            let fileReader = new FileReader();
            fileReader.readAsDataURL(form.photo);
            fileReader.addEventListener('load', () => {
                form.url = fileReader.result
            });
        }
    }

    async function getDepartemen() {
        let {data} = await bsoft.axios.get('/departement');
        if (data.code === 200) departements.value = data.data;
    }

    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        form.value = {};
        if (id) {
            useBoedysoft().axios.get('/user/' + id).then(({data}) => {
                Object.assign(form, data.data);
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
        let data_form = new FormData();
        if (form.id) data_form.append('id', form.id);
        data_form.append('name', form.name);
        data_form.append('email', form.email);
        data_form.append('password', form.password);
        data_form.append('departement_id', form.departement_id);
        data_form.append('perusahaan_id', form.perusahaan_id);
        data_form.append('photo', form.photo);
        if (form.id != undefined) {
            let {data} = await bsoft.axios.post('/user/update/' + form.id, data_form);
            if (data.code === 200) {
                sukses(data);
            } else {
                errors.value = data.data;
            }
        } else {
            let {data} = await bsoft.axios.post('/user', data_form);
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
