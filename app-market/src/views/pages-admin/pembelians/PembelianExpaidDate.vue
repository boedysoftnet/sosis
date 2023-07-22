<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/4 shadow bg-basic-50 h-max-[450px]">
                <div class="py-1 px-5 font-semibold border-b">Set Expaid Date</div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan">
                        <div class="grid grid-cols-1">
                            <input type="date" ref="refExpaidDate" step="any" v-model="form.expaid"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full"
                                   placeholder="NB: 10%">
                            <ValidateError :errors="errors" name="expaid"></ValidateError>
                        </div>
                        <div class="pt-3 text-right">
                            <button type="button" class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50"
                                    @click.prevent="dismisDialog"><span class="dx-icon-back "></span> Batal
                            </button>
                            <button class="py-1 px-2 shadow ml-2 bg-basic-500 text-basic-50"><span
                                    class="dx-icon-save"></span> Terapkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>
<script setup>
    import {ref, reactive, onMounted, onActivated} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';

    const dialog = new ref(false);
    const bsoft = useBoedysoft();
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const form = reactive({
        id: null,
        expaid: null
    })
    const refExpaidDate = ref();

    function showDialog(it) {
        dialog.value = true;
        errors.value = {};
        form.id = it.id;
        form.harga = it.harga;
        setTimeout(() => {
            refExpaidDate.value.focus();
        }, 100)
    }

    function dismisDialog() {
        dialog.value = false;
    }

    function sukses(res) {
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: res.message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            emit('onRefresh');
            dismisDialog();
        })
    }

    async function simpan() {
        let {data} = await bsoft.axios.post('/pembelian/detail/expaid/' + form.id, form);
        if (data.code === 200) {
            sukses(data);
        } else {
            errors.value = data.data;
        }
    }


    defineExpose({
        showDialog,
    });
</script>
<style scoped>

</style>
