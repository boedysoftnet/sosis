<template>
    <div class="fixed inset-0 bg-cover" style="background-image: url('/images/login-background.jpeg')">
        <div class="bg-indigo-800 absolute inset-0 bg-opacity-50 z-10"></div>
        <div class="flex justify-center items-center h-full relative z-20 overflow-auto">
            <div class="flex-1 flex justify-center items-center">
                <form @submit.prevent="masuk"
                      class="scroll-smooth flex flex-col md:w-1/2 bg-opacity-10 md:bg-opacity-20 text-basic-800 py-10 px-6 shadow-lg rounded bg-basic-50">
                    <div class="text-center mb-2 text-white">
                        <fa-icon icon="sun" class="text-4xl animate-bounce"></fa-icon>
                        <h2 class="text-2xl uppercase font-semibold drop-shadow" >MANAGEMENT SYSTEM POS SALE</h2>
                        <span class="text-sm italic" >Apakah anda sudah memiliki akun..?, Jika belum kontak cs kami : 081805716533</span>
                    </div>
                    <div class="flex-1 relative" >
                        <label for="" class="text-white">Email</label>
                        <input type="email" class="" v-model="form.email">
                    </div>
                    <div class="flex-1">
                        <label for="" class="text-white">Password</label>
                        <div class="flex" >
                            <input :type="eye?'password':'text'" class="border-none" v-model="form.password">
                            <button class="border-none bg-white" @click="eye=!eye" type="button">
                                <fa-icon icon="eye"></fa-icon>
                            </button>
                        </div>
                    </div>
                    <div class="flex mt-4 divide-x-2 divide-red-200 space-x-1 justify-end">
                        <button class="bg-basic-300 border-none rounded-md py-1 px-5 font-semibold" type="button" @click.prevent="showInfoRegister" >Buat Akun..?
                        </button>
                        <button class="bg-basic-50 border-none rounded-md py-1 px-5 font-semibold"> Masuk
                            <fa-icon icon="arrow-right"></fa-icon>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {ref, reactive, onMounted} from 'vue';
    import {useBoedysoft} from "../../../stores/boedysoft";
    import * as Swal from "sweetalert2";
    import {useRouter} from "vue-router";

    const bsoft = useBoedysoft();
    const router = useRouter();
    const form = reactive({
        email: '',
        password: ''
    });
    const eye=ref(true)

    function masuk() {
        bsoft.axios.post('/user/auth', form).then(res => {
            if (res.data.code === 200) {
                localStorage.setItem('key_user', res.data.data.token_id);
                window.location.href='/';
            } else {
                Swal.fire({
                    icon: "error",
                    title: 'Peringatan!',
                    text: 'Maaf user dan password anda tidak falid..!'
                })
            }
        })
    }
    function showInfoRegister() {
        Swal.fire({
            icon:"info",
            title:'Halo, Silakan Kontak Kami',
            text:'Untuk register akun anda dapat menggunakan via WA : 081805716533'
        });
    }
</script>

<style scoped>

</style>
