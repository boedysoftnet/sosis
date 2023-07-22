<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/2 shadow bg-basic-50 h-max-[450px]">
                <div class="py-3 px-5 font-semibold border-b">Register Barang</div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan">
                        <div>
                            <div class="px-2 py-1 mt-2 border border-basic-300 rounded">
                                <ul class="flex justify-between items-center">
                                    <li @click.prevent="tabs=0" class="text-center w-full cursor-pointer" :class="{'bg-basic-500 text-basic-50 bg-opacity-50 font-semibold':tabs===0}">Master</li>
                                    <li @click.prevent="tabs=1" class="text-center w-full cursor-pointer border-l"  :class="{'bg-basic-500 text-basic-50 bg-opacity-50 font-semibold':tabs===1}">Harga Jual/Multi Satuan</li>
                                </ul>
                            </div>
                            <div v-if="tabs===0" class="py-2 shadow p-2 mt-2 border rounded border-basic-200">
                                <div class="mt-2">
                                    <label for="">Nama Barang</label>
                                    <input type="text" v-model="form.nama_barang"
                                           class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                    <ValidateError :errors="errors" name="nama_barang"></ValidateError>
                                </div>
                                <div class="mt-2">
                                    <label for="">Kategori</label>
                                    <select v-model="form.barang_kategori_id"
                                            class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                        <option :value="it.id" v-for="it in listBarangKategori">{{it.kategori}}</option>
                                    </select>
                                    <ValidateError :errors="errors" name="nama_barang"></ValidateError>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="mt-2">
                                        <label for="">Pokok</label>
                                        <input type="number" v-model="form.pokok"
                                               class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                        <ValidateError :errors="errors" name="pokok"></ValidateError>
                                    </div>
                                    <div class="mt-2 flex items-center space-x-2 pt-5">
                                        <input type="checkbox" id="jasa" v-model="form.type_jasa" value="1" class="rounded-md">
                                        <div>
                                            <label for="jasa" class="uppercase font-semibold underline">Type Jasa</label>
                                            <p class="text-xs text-orange-500 italic">Jika barang ini memiliki jenis jasa / tidak memiliki stock</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label for="">Deskripsi</label>
                                    <input type="text" v-model="form.deskripsi"
                                           class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full">
                                    <ValidateError :errors="errors" name="deskripsi"></ValidateError>
                                </div>
                            </div>
                            <div v-if="tabs===1" class="py-2 shadow p-2 mt-2 border rounded border-basic-200 h-[280px] overflow-auto">
                                <div class="flex justify-between items-center border-b">
                                    <div>Satuan Barang</div>
                                    <a href="" @click.prevent="addSatuanBarang()"><span class="dx-icon-plus"></span></a>
                                </div>
                                <div v-for="(it,index) in form.barang_satuan" class="grid grid-cols-4 gap-2 mb-2">
                                    <div class="col-span-2">
                                        <label for="">Satuan Ke-{{index+1}}</label>
                                        <div ><input type="text" v-model="form.barang_satuan[index].satuan"
                                                     class="py-1 px-2 border outline-none focus:border-basic-600 border-basic-300 w-full">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="">Isi</label>
                                        <div class="flex items-center">
                                            <input type="number" :readonly="index===0" step="any" v-model="form.barang_satuan[index].isi"
                                                   class="py-1 px-2 border outline-none focus:border-basic-600 border-basic-300 w-full" :class="{'bg-[grey] bg-opacity-20':index===0}">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="">Harga</label>
                                        <div class="flex items-center">
                                            <input type="number" step="any" v-model="form.barang_satuan[index].harga"
                                                   class="py-1 px-2 border outline-none focus:border-basic-600 border-basic-300 w-full">
                                            <a href="" @click.prevent="remove(index)" class="dx-icon-remove px-2" v-if="index!=0"></a>
                                        </div>
                                    </div>
                                </div>
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
    let form = reactive({
        barang_satuan: []
    });
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});

    onMounted(() => {
        getBarangKategori();
    });

    const listBarangKategori = ref([]);

    async function getBarangKategori() {
        let {data} = await bsoft.axios.get('/barang-kategori');
        if (data.code === 200) listBarangKategori.value = data.data;
    }

    function addSatuanBarang() {
        form.barang_satuan.push({
                barang_id: 1,
                satuan: 'pcs',
                isi: 1,
                harga: 1000
            }
        )
    }

    function remove(key) {
        form.barang_satuan.splice(key, 1);
    }

    /*todo jangan menyerah cari terus sampai ketemu untuk kelola array reactive yach budi*/
    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        tabs.value=0;
        Object.assign(form, {
            nama_barang:null,
            barang_kategori_id:null,
            pokok:null,
            deskripsi:null,
            barang_satuan:[]
        });
        addSatuanBarang();
        if (id) {
            useBoedysoft().axios.get('/barang/' + id).then(({data}) => {
                Object.assign(form,data.data);
                form.pokok=form.pokok_terakhir;
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
        }).then(() => {
            emit('setBarang',res.data.id);
            dismisDialog();
        })
    }

    async function simpan() {
        if (form.id != undefined) {
            let {data} = await bsoft.axios.put('/barang/' + form.id, form);
            if (data.code === 200) {
                sukses(data);
            } else {
                errors.value = data.data;
            }
        } else {
            let {data} = await bsoft.axios.post('/barang', form);
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
