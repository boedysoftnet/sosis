<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50 h-max-[450px] ">
                <div class="py-3 px-5 font-semibold border-b">Register Departement</div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan">
                        <div class="mt-2 space-y-3">
                            <div>
                                <label for="">Nama Departement</label>
                                <input type="text" v-model="form.nama"
                                       class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                <ValidateError :errors="errors" name="nama_departement"></ValidateError>
                            </div>
                            <div>
                                <label for="" class="font-semibold uppercase">Aktifkan Rules Navigasi</label>
                                <div class="py-1" v-for="(its,key) in bsoft.groupBy(form.list_rules,'kategori')">
                                    <div class="px-2 py-1 font-semibold uppercase border-b bg-basic-500 bg-opacity-20 rounded-md"
                                         v-if="key!='null'">{{key}}
                                    </div>
                                    <ul class="grid grid-cols-3 gap-x-2">
                                        <li v-for="it in its"><input type="checkbox" :id="it.id"
                                                                     v-model="form.rules[it.id]" :value="true"> <label
                                                :for="it.id"
                                                class="cursor-pointer">{{it.nama}}</label>
                                        </li>
                                    </ul>
                                </div>
                                <ValidateError :errors="errors" name="nama_departement"></ValidateError>
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

    const dialog = new ref(false);
    let form = reactive({});
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});

    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        Object.assign(form, {
            nama: null,
            rules: {},
            list_rules: []
        });
        useBoedysoft().axios.get('/navigator', {params: {all: true}}).then(({data}) => {
            Object.assign(form.list_rules, data.data);
            form.list_rules.forEach(it => {
                form.rules[it.id] = true;
            })
        })
        if (id) {
            bsoft.axios.get('/departement/' + id).then(res => {
                if (res.data.code === 200) {
                    Object.assign(form, res.data.data);
                }
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
            let {data} = await bsoft.axios.put('/departement/' + form.id, form);
            if (data.code === 200) {
                sukses(data);
            } else {
                errors.value = data.data;
            }
        } else {
            let {data} = await bsoft.axios.post('/departement', form);
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
