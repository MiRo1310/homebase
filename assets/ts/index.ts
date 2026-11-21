import {InitObject} from "./types/types.ts";
import icons from './icons/index.ts'

const elements: InitObject[] = [icons]

const init = () => {
    elements.forEach((element) => {
            element.init()
        }
    )
}
init()
