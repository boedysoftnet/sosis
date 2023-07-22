<template>
    <div class="bg-gray-200 relative">
        <div class="h-32 bg-basic-800 hidden 2xl:flex"></div>
        <div class="bg-white overflow-hidden h-screen flex 2xl:w-[1600px] mx-auto 2xl:-mt-24" :class="{'dark':darkStatus}">
            <div class="border-r border-basic-300 absolute w-full lg:w-auto bg-basic-50 z-20 lg:relative overflow-auto scrollbar scrollbar-thumb-gray-300 scrollbar-track-gray-100 transform transition duration-300 " :class="{'lg:w-0 w-0 -translate-x-full':!showMenu}">
                <navigator @changeDarkMode="onChangeDarkMode" @setToggleMenu="setToggleMenu" :daskStatus="darkStatus"></navigator>
            </div>
            <div class="flex-1 flex flex-col overflow-auto scrollbar scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                <div class="bg-basic-500 py-2 px-2">
                    <a href="" @click.prevent="setToggleMenu"><span class="fa fa-navicon text-white"></span></a>
                </div>
                <div class="flex p-5 flex-col dark:bg-black dark:text-gray-300 flex-1 h-screen">
                    <RouterView></RouterView>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import Navigator from '../../components/pages-admin/Navigator.vue'
    import {ref} from 'vue';

    const darkStatus = ref(false);
    const showMenu=ref(true);

    function setToggleMenu() {
        showMenu.value=!showMenu.value;
        localStorage.setItem('menu',showMenu.value);
    }
    function onChangeDarkMode() {
        darkStatus.value = !darkStatus.value;
    }
</script>
