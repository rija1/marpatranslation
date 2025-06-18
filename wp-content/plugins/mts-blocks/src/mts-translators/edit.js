import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';
import { TextControl } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
  const [terms, setTerms] = useState([]);
  const [filteredTerms, setFilteredTerms] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const blockProps = useBlockProps();

  const fetchTerms = async () => {
    try {
      const response = await apiFetch({
        path: '/pods/v1/tibetan-terms',
        method: 'GET',
      });
      setTerms(response);
      setFilteredTerms(response);
    } catch (error) {
      console.error('Error fetching terms:', error);
    }
  };

  const handleSearch = (value) => {
    setSearchTerm(value);
    const filtered = terms.filter(term => 
      term.tibetan.toLowerCase().includes(value.toLowerCase()) ||
      term.english.toLowerCase().includes(value.toLowerCase())
    );
    setFilteredTerms(filtered);
  };

  useEffect(() => {
    fetchTerms();
  }, []);

  return (
    <div {...blockProps}>
      <TextControl
        label={__('Search Tibetan Terms', 'mts-tibetan-terms')}
        value={searchTerm}
        onChange={handleSearch}
        placeholder={__('Type to filter terms...', 'mts-tibetan-terms')}
      />
      <div>
        {filteredTerms.map(term => (
          <div key={term.id}>
            <strong>{term.tibetan}</strong> - {term.english}
          </div>
        ))}
      </div>
    </div>
  );
}
