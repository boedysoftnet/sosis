<template>
    <div v-if="user!=null" class="bg-dashboard">
        <div class="flex justify-between flex-col items-center px-2 cursor-pointer text-xl sticky top-0 bg-white mb-2">
            <div class="w-full flex items-center justify-center border-b border-dashed border-basic-400 bg-white">
                <div class="flex items-center" @click.prevent="router.push({name:'dashboard'})">
                    <h3 class="font-semibold">{{user.perusahaan.nama}}</h3>
                </div>
            </div>
        </div>
        <div class="py-4 space-y-2">
            <img :src="user.url" alt="" class="rounded-full w-32 mx-auto h-32 shadow">
            <div class="text-center">
                <h2 class="text-xl font-semibold">{{user.name}}</h2>
                <p class="underline">{{user.email}}</p>
                <p class="italic">#{{user.departement.nama}}#</p>
            </div>
        </div>
        <div>
            <div class="pt-3 w-full sticky top-4">
                <input type="search" v-model="search" @keyup="filterData"
                       class="py-1 outline-none focus:border-basic-400 px-2 w-full border-t border-b border-basic-300"
                       placeholder="Cari Menu..?">
            </div>
            <div class="" v-for="(its,key) in bsoft.groupBy(navigators,'kategori')">
                <div class="px-2 py-1 font-semibold uppercase border-b bg-basic-100" v-if="key!='null'">{{key}}</div>
                <ul class="px-2 ml-2">
                    <li v-for="it in its">
                        <RouterLink :to="{name:it.route}" @click.prevent="navMenu" class="w-full flex justify-between">
                            {{it.nama}}
                        </RouterLink>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</template>

<script setup>
    import {ref, onMounted, watchEffect, watch} from 'vue';
    import {useBoedysoft} from "../../stores/boedysoft";
    import {useRouter} from "vue-router";

    const navigators = ref([])
    const bsoft = useBoedysoft();
    const search = ref('');
    const emit = defineEmits();
    const router = useRouter();
    const user = ref(null);

    const props = defineProps({
        darkStatus: Boolean,
    });

    function navMenu() {
        emit('setToggleMenu');
    }

    function filterData() {
        if (search.value) {
            navigators.value = navigators.value.filter((e) => e.nama.toLowerCase().includes(search.value.toLowerCase()))
        } else {
            getData()
        }
    }

    async function getData() {
        let {data} = await bsoft.axios.get('/navigator');
        if (data.code === 200) {
            navigators.value = data.data
        }
    }

    async function getProfileUser() {
        let {data} = await bsoft.axios.get('/user/profile');
        if (data.code === 200) {
            user.value = data.data
        }
    }


    onMounted(() => {
        getData();
        getProfileUser();
    })

    watch(search, () => {
        console.log('Search', search.value)
    })

    function onChangeDarkMode() {
        emit('changeDarkMode');
    }
</script>
