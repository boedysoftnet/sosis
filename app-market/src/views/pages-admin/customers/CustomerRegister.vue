<template>
    <transition>
        <div v-if="dialog" class="absolute inset-0 z-10 bg-basic-500 bg-opacity-50 flex justify-center items-center">
            <div class="w-1/2 bg-basic-50 overflow-hidden">
                <div class="p-3 bg-basic-700 text-basic-50">
                    <h2>Register Customer</h2>
                </div>
                <Form class="p-3" @submit.prevent="kirim">
                    <div>
                        <label for="">Nama Lengkap</label>
                        <input type="text" v-model="form.nama"/>
                        <validate-error name="nama" :errors="errors"/>
                    </div>
                    <div>
                        <label for="">Alamat</label>
                        <input type="text" v-model="form.alamat"/>
                        <validate-error name="alamat" :errors="errors"/>
                    </div>
                    <div>
                        <label for="">Telpon</label>
                        <input type="text" v-model="form.telpon"/>
                        <validate-error name="telpon" :errors="errors"/>
                    </div>
                    <div>
                        <label for="">Email</label>
                        <input type="text" v-model="form.email"/>
                        <validate-error name="email" :errors="errors"/>
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
                    <div>
                        <label for="">Catatan</label>
                        <input type="text" v-model="form.catatan"/>
                        <validate-error name="catatan" :errors="errors"/>
                    </div>
                    <div class="mt-2 flex justify-end space-x-2">
                        <button @click.prevent="dismiss" type="button">
                            <fa-icon icon="arrow-left"></fa-icon>
                            Tutup
                        </button>
                        <button>
                            <fa-icon icon="save"></fa-icon>
                            Proses
                        </button>
                    </div>
                </Form>
            </div>
        </div>
    </transition>
</template>

<script setup>
    import {ref, reactive, onMounted, computed} from 'vue';
    import {DxDataGrid, DxColumn, DxFilterRow, DxPaging} from 'devextreme-vue/data-grid';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';

    const emit = defineEmits();
    const bsoft = useBoedysoft();
    const errors = ref({});
    const dialog = ref(false);
    const meta = {
        id: null,
        nama: null,
        alamat: null,
        email: null,
        telpon: null,
        catatan: null,
        photo: null,
        url:null
    }

    const form = reactive({...meta});

    function showDialog(id = null) {
        dialog.value = true;
        Object.assign(form, meta)
        if (id != null) {
            bsoft.axios.get(`/customer/${id}`).then(res => {
                if (res.data.code === 200) Object.assign(form, res.data.data)
                console.log(form.url)
            });
        }
    }

    function dismiss() {
        dialog.value = false;
    }

    function kirim() {
        let data=new FormData();
        if(form.id) data.append('id',form.id);
        data.append('nama',form.nama);
        data.append('alamat',form.alamat);
        data.append('email',form.email);
        data.append('telpon',form.telpon);
        data.append('photo',form.photo);
        bsoft.axios.post('/customer',data).then(res => {
            if (res.data.code === 200) {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: res.data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    dismiss()
                    emit('onRefresh')
                    emit('setCustomer',res.data.data.id)
                    Object.assign(form, meta)
                });
            } else {
                errors.value = res.data.data;
            }
        })
    }

    function selectImage(event) {
        if (event.target.files.length != 0) {
            form.photo = event.target.files[0];
            let fileReader=new FileReader();
            fileReader.readAsDataURL(form.photo);
            fileReader.addEventListener('load',()=>{form.url=fileReader.result});
        }
    }

    defineExpose({
        showDialog,
        dismiss
    })
</script>

<style scoped>

</style>
