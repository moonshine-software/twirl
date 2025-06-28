interface Callbacks {
    [key: string]: Function;
}

interface MoonShine {
    callbacks: Callbacks;
}

interface Window {
    MoonShine: MoonShine;
}