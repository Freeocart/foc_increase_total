import React from 'react'
import { ConfigProvider } from './components/ConfigProvider'
import Rules from './components/Rules'

import './App.css'

function App({ rules, ocInfo, outputName }) {
  return (
    <ConfigProvider rules={ rules } ocInfo={ ocInfo }>
      <div className="App">
        <Rules outputName={ outputName } />
      </div>
    </ConfigProvider>
  );
}

export default App;
