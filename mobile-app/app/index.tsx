import { WebView } from 'react-native-webview';
import { branding } from '@/constants/branding';

export default function Home() {
    return (
        <WebView source={{ uri: branding.url }} />
    );
}
