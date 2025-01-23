// BH-Board 프레임워크 타입 정의

// 이벤트 리스너 파라미터 타입
interface EventListenerParams {
    obj: Element | Document;
    eventName: string;
    selector: string;
    callback: (e: Event, obj: Element) => void;
}

// DOM 확장 인터페이스
interface Element {
    /**
     * 동적으로 추가되는 자식 요소에 이벤트를 바인딩
     * @param eventName 이벤트 이름 ('click', 'change' 등)
     * @param selector CSS 선택자
     * @param callback 이벤트 핸들러 함수
     */
    addEventListenerChild(
        eventName: string,
        selector: keyof HTMLElementTagNameMap | string,
        callback: (e: Event, obj: Element) => void
    ): void;

    /**
     * 기본 동작을 방지하는 이벤트 리스너 추가
     * @param eventName 이벤트 이름
     * @param callback 이벤트 핸들러 함수
     */
    preventAddEventListener(
        eventName: string,
        callback: (e: Event) => void
    ): (e: Event) => void;

    /**
     * 현재 요소 뒤에 새로운 노드 삽입
     * @param newNode 삽입할 새로운 노드
     */
    insertAfter(newNode: Node): void;
}

// Document 확장 인터페이스
interface Document {
    /**
     * 동적으로 추가되는 요소에 이벤트를 바인딩
     * @param eventName 이벤트 이름 ('click', 'change' 등)
     * @param selector CSS 선택자
     * @param callback 이벤트 핸들러 함수
     */
    addEventListenerChild(
        eventName: string,
        selector: string,
        callback: (e: Event, obj: Element) => void
    ): void;

    /**
     * 문서 로드 완료 시 실행할 콜백 등록
     * @param callback 실행할 함수
     */
    ready(callback: () => void): void;
}
