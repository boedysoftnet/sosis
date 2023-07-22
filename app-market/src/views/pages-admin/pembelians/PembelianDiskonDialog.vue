<template>
    <transition>
        <div v-if="dialog" class="fixed inset-0 bg-basic-800 bg-opacity-70 z-10 flex items-center justify-center">
            <div class="md:w-1/4 shadow bg-basic-50 h-max-[450px]">
                <div class="py-1 px-5 font-semibold border-b">Set Diskon</div>
                <div class="py-3 px-5">
                    <form @submit.prevent="simpan">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" ref="refDiskonGlobal" step="any" v-model="diskonGlobal"
                                   class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full"
                                   placeholder="NB: 10%">
                            <ValidateError :errors="errors" name="diskonGlobal"></ValidateError>
                            <select class="py-1 px-2 outline-none focus:border-basic-500 border border-basic-200 w-full" v-model="jenisDiskon">
                                <option value="%">%</option>
                                <option value="nominal">Nominal</option>
                            </select>
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
    import {ref, reactive, onMounted,onActivated} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import ValidateError from '../../../components/pages-admin/ValidateError.vue';
    import Swal from 'sweetalert2';

    const tabs = ref(0);
    const dialog = new ref(false);
    const diskonGlobal = ref(0);
    const bsoft = useBoedysoft();
    const props = defineProps({
        pembelian_id: String
    });
    const emit = defineEmits(['onRefresh']);
    const errors = ref({});
    const jenisDiskon=ref('nominal');
    const refDiskonGlobal=ref();

    function showDialog(id = null) {
        dialog.value = true;
        errors.value = {};
        setTimeout(()=>{
            refDiskonGlobal.value.focus();
        },100)
    }

    function dismisDialog() {
        dialog.value = false;
        emit('onRefresh');
    }

    function sukses(res) {
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: res.message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => dismisDialog())
    }

    async function simpan() {
        let {data} = await bsoft.axios.post('/pembelian/diskon/' + props.pembelian_id, {diskonGlobal:diskonGlobal.value,jenisDiskon:jenisDiskon.value});
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
