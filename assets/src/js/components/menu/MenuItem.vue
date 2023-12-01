<template>
    <a :href="url" :class="(active ? 'bg-gray-400/50 border-l-2' : 'grayscale px-4') + ' flex basis-auto px-2 rounded md:px-0 mr-3 py-3 my-1 md:rounded-r-full md:pl-3 border-blue-500 text-center'">
        <img v-if="image" :src="image" class="w-[20px] mr-3 ml-1">
        <i v-else-if="icon" :class="'w-[20px] block mr-3 ml-1 ' + icon"></i>
        <slot></slot>
    </a>
</template>
<script>
export default {
    props: ['image', 'icon', 'url', 'active']
}
</script>
