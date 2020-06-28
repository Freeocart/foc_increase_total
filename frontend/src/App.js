import React from 'react'
import { ConfigProvider } from './components/ConfigProvider'
import Rules from './components/Rules'

import './App.css'
import ExportImport from './components/ExportImport'

function App({ rules, ocInfo, outputName }) {
  return (
    <ConfigProvider rules={ rules } ocInfo={ ocInfo }>
      <div className="App">
        <Rules outputName={ outputName } />
        <ExportImport />
      </div>
    </ConfigProvider>
  );
}

export default App;
