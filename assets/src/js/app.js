import { createApp } from "vue";
import Backend from "./components/Backend.vue";

const element = document.querySelector("#backend-ui");

const app = createApp({
    ...element.dataset,
    components: {Backend}
});

app.mount(element);

