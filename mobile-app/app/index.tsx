import { useRef, type ComponentType, type RefAttributes } from 'react';
import { PermissionsAndroid, Platform } from 'react-native';
import { WebView } from 'react-native-webview';
import type { AndroidWebViewProps } from 'react-native-webview/lib/WebViewTypes';
import { branding } from '@/constants/branding';

const LocationWebView = WebView as unknown as ComponentType<AndroidWebViewProps & RefAttributes<WebView>>;

export default function Home() {
    const webview = useRef<WebView>(null);
    async function onMessage(event: { nativeEvent: { data: string } }) {
        if (event.nativeEvent.data !== 'request-location-permission') return;
        let granted = true;
        if (Platform.OS === 'android') {
            try {
                granted = (await PermissionsAndroid.request(PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION)) === PermissionsAndroid.RESULTS.GRANTED;
            } catch { granted = false; }
        }
        webview.current?.injectJavaScript(`window.locationPermissionResult && window.locationPermissionResult(${granted}); true;`);
    }
    return <LocationWebView ref={webview} source={{ uri: branding.url }} geolocationEnabled onMessage={onMessage} />;
}
