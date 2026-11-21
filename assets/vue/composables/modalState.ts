import {ref} from "vue";

class ModalState {
    private _open = ref(false)
    private _status = ref<boolean | null>(null)

    get isOpen(): boolean {
        return this._open.value;
    }

    public close(): void {
        this._open.value = false;
    }

    public toggle(): void {
        this._open.value = !this._open.value;
    }

    public setStatus(status: boolean | null): void {
        this._status.value = status;
    }

    get status() {
        return this._status.value;
    }
}

export {ModalState}
