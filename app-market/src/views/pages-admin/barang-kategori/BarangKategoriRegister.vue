<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50">
                <div class="py-3 px-5 font-semibold border-b">Register Barang Kategori</div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan()">
                        <div class="mt-2">
                            <label for="">Kategori</label>
                            <input type="text" v-model="form.kategori"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                            <ValidateError :errors="errors" name="kategori"></ValidateError>
                        </div>
                        <div class="mt-2">
                            <label for="">Deskripsi</label>
                            <input type="text" v-model="form.deskripsi"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                            <ValidateError :errors="errors" name="deskripsi"></ValidateError>
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

    import {ref, reactive} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';

    const dialog = new ref(false);
    let form = ref({});
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});

    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        form.value = {};
        if (id) {
            useBoedysoft().axios.get('/barang-kategori/' + id).then(({data}) => {
                form.value = data.data;
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
        if (form.value.id != undefined) {
            let {data} = await bsoft.axios.put('/barang-kategori/' + form.value.id, form.value);
            if (data.code === 200) {
                sukses(data);
            } else {
                errors.value = data.data;
            }
        } else {
            let {data} = await bsoft.axios.post('/barang-kategori', form.value);
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
