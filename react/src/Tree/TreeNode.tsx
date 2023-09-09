import { useCallback } from "react";
import { DataNode, useTreeState } from "./TreeState";
import OpenCloseButton from "./OpenCloseButton";
import FolderIcon from "./FolderIcon";
import DeleteButton from "./DeleteButton";
import AddButton from "./AddButton";

type Props = {
  node: DataNode;
};

const TreeNode = ({ node }: Props) => {
  const {
    openFolders,
    setFolderClosed,
    setFolderOpen,
    removeFolder,
    addFolder,
  } = useTreeState();

  const toggleFolder = useCallback(() => {
    if (openFolders.includes(node.id)) {
      setFolderClosed(node.id);
    } else {
      setFolderOpen(node.id);
    }
  }, [openFolders, setFolderClosed, setFolderOpen, node.id]);

  const addFolderPrompt = useCallback(
    (nodeId: number) => {
      const folderName = prompt("Bitte Namen für neuen Ordner eingeben:");
      if (folderName === null) {
        return;
      }

      if (!folderName) {
        alert("Fehler: Es wurde kein Ordnername eingegeben.");
        return;
      }

      addFolder(folderName, nodeId);
    },
    [addFolder]
  );

  return (
    <li className="tree-node">
      <div className="tree-node-current">
        <OpenCloseButton
          hasChildren={node.children.length > 0}
          onClick={toggleFolder}
          open={openFolders.includes(node.id)}
        />
        <FolderIcon open={openFolders.includes(node.id)} />
        {node.name}
        <DeleteButton onClick={() => removeFolder(node.id)} />
        <AddButton onClick={() => addFolderPrompt(node.id)} />
      </div>
      {node.children.length > 0 && openFolders.includes(node.id) && (
        <ul className="tree-node-children">
          {node.children.map((childNode) => (
            <TreeNode key={childNode.id} node={childNode} />
          ))}
        </ul>
      )}
    </li>
  );
};

export default TreeNode;
