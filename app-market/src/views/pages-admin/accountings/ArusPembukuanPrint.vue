<template>
    <div>
        <!-- SOURCE -->
        <div id="printMe" v-if="akunPerkiraan!=null">
            <div class="px-10 py-5">
                <cover-perusahaan/>
                <div class="underline uppercase font-semibold text-center text-4xl mt-5 mb-3">
                    <div>
                        <div>
                            <div>{{akunPerkiraan.nama_akun}}</div>
                        </div>
                    </div>
                </div>
                <div>
                    <table class="w-full">
                        <thead>
                        <tr>
                            <th>Catatan</th>
                            <th>Debet</th>
                            <th>Kredit</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="it in akunPerkiraan.jurnal">
                            <td class="py-1 px-2">
                                <div>
                                    <p>{{it.catatan}}</p>
                                    <div class="text-xs flex justify-between"><div>{{it.created_at}}</div> <div>By.{{it.user.name}}</div></div>
                                </div>
                            </td>
                            <td class="py-1 px-2 text-right">{{bsoft.NumberFormat(it.debet)}}</td>
                            <td class="py-1 px-2 text-right">{{bsoft.NumberFormat(it.kredit)}}</td>
                        </tr>
                        </tbody>
                        <thead>
                            <tr>
                                <th >Grand Total</th>
                                <th >{{bsoft.NumberFormat(akunPerkiraan.debet_sum)}}</th>
                                <th>{{bsoft.NumberFormat(akunPerkiraan.kredit_sum)}}</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="mt-5 text-2xl font-semibold flex justify-end flex-col items-end">
                        <h2>Saldo {{akunPerkiraan.nama_akun}} s/d {{props.periode}}</h2>
                        <div class="border-b border-b-2 shadow py-3 px-5 mt-4 bg-basic-200">Rp.{{bsoft.NumberFormat(akunPerkiraan.saldo_akun)}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";

    const bsoft = useBoedysoft();
    const emit = defineEmits();
    const akunPerkiraan=ref(null);
    const props=defineProps(['periode']);

    async function showPrint(id) {
        /*todo lanjutkan untuk print arus pembukuan*/
        let {data} = await bsoft.axios.get('/jurnal/arus-pembukuan/' + id);
        if (data.code === 200) {
            akunPerkiraan.value=data.data;
            return setTimeout(()=>{
                bsoft.printPeper('printMe');
                emit('onFinish');
            },500);
        }
        return false;
    }

    defineExpose({
        showPrint
    })
</script>

<style scoped>

</style>
