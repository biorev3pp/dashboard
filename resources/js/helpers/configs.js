import { setConfigs } from "./general";

export function getSetting() {
    const userStr = localStorage.getItem("settings");

    if (!userStr) {
        return null;
    }
    return JSON.parse(userStr);

}